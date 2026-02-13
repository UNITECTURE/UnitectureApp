<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Carbon\Carbon;
use App\Models\Attendance;
use App\Models\AttendanceLog;
use App\Models\ManualAttendanceRequest;
use App\Models\Leave;
use Illuminate\Support\Facades\Storage;
use ZipArchive;

class MonthlyBackup extends Command
{
    protected $signature = 'backup:monthly {--month= : Month to backup (YYYY-MM)} {--dry-run : Show what would be backed up}';
    protected $description = 'Create monthly backup of attendance and leave data';

    public function handle()
    {
        $dryRun = $this->option('dry-run');

        // Determine which month to backup
        if ($this->option('month')) {
            $month = Carbon::parse($this->option('month') . '-01');
        } else {
            // Backup previous month by default
            $month = Carbon::now()->subMonth()->startOfMonth();
        }

        $monthStr = $month->format('Y-m');
        $startDate = $month->copy()->startOfMonth();
        $endDate = $month->copy()->endOfMonth();

        $this->info("=== Monthly Backup Tool ===");
        $this->info("Backing up data for: {$monthStr}");
        $this->info("Date Range: {$startDate->toDateString()} to {$endDate->toDateString()}");
        $this->info("Mode: " . ($dryRun ? "DRY RUN" : "LIVE"));
        $this->newLine();

        // Count records
        $counts = [
            'attendance' => Attendance::whereBetween('date', [$startDate, $endDate])->count(),
            'attendance_logs' => AttendanceLog::whereBetween('timestamp', [$startDate, $endDate])->count(),
            'manual_requests' => ManualAttendanceRequest::whereBetween('date', [$startDate, $endDate])->count(),
            'leaves' => Leave::where(function ($query) use ($startDate, $endDate) {
                $query->whereBetween('start_date', [$startDate, $endDate])
                    ->orWhereBetween('end_date', [$startDate, $endDate]);
            })->count(),
        ];

        $this->table(
            ['Table', 'Records to Backup'],
            array_map(fn($k, $v) => [$k, number_format($v)], array_keys($counts), $counts)
        );

        $totalRecords = array_sum($counts);

        if ($totalRecords === 0) {
            $this->warn("No records found for {$monthStr}!");
            return 0;
        }

        if ($dryRun) {
            $this->warn("\n✓ DRY RUN COMPLETE - No files were created");
            return 0;
        }

        // Create backup directory
        $backupDir = "backups/monthly/{$month->year}";
        Storage::makeDirectory($backupDir);

        // Export data
        $this->info("\nExporting data to CSV...");
        $files = [];

        $files[] = $this->exportAttendance($startDate, $endDate, $monthStr, $backupDir);
        $files[] = $this->exportLogs($startDate, $endDate, $monthStr, $backupDir);
        $files[] = $this->exportManualRequests($startDate, $endDate, $monthStr, $backupDir);
        $files[] = $this->exportLeaves($startDate, $endDate, $monthStr, $backupDir);

        // Create ZIP archive
        $this->info("\nCreating ZIP archive...");
        $zipFilename = "{$backupDir}/backup_{$monthStr}.zip";
        $this->createZipArchive($files, $zipFilename);

        // Delete individual CSV files
        foreach ($files as $file) {
            Storage::delete($file);
        }

        $zipPath = Storage::path($zipFilename);
        $zipSize = filesize($zipPath);

        $this->newLine();
        $this->info("✓ Backup complete!");
        $this->info("Archive: {$zipFilename}");
        $this->info("Size: " . $this->formatBytes($zipSize));
        $this->info("Records: " . number_format($totalRecords));

        return 0;
    }

    private function exportAttendance($startDate, $endDate, $monthStr, $backupDir)
    {
        $filename = "{$backupDir}/attendance_{$monthStr}.csv";

        $records = Attendance::whereBetween('date', [$startDate, $endDate])
            ->with('user')
            ->orderBy('date')
            ->get();

        $csv = "User ID,User Name,Date,Clock In,Clock Out,Duration,Status,Type,Late Marks,Created At\n";
        foreach ($records as $record) {
            $csv .= sprintf(
                "%s,%s,%s,%s,%s,%s,%s,%s,%s,%s\n",
                $record->user_id,
                $this->escapeCsv($record->user->full_name ?? 'Unknown'),
                $record->date,
                $record->clock_in ?? '',
                $record->clock_out ?? '',
                $record->duration ?? '',
                $record->status,
                $record->type ?? '',
                $record->late_marks ?? '0',
                $record->created_at
            );
        }

        Storage::put($filename, $csv);
        $this->line("  ✓ Exported attendance: {$records->count()} records");

        return $filename;
    }

    private function exportLogs($startDate, $endDate, $monthStr, $backupDir)
    {
        $filename = "{$backupDir}/attendance_logs_{$monthStr}.csv";

        $records = AttendanceLog::whereBetween('timestamp', [$startDate, $endDate])
            ->orderBy('timestamp')
            ->get();

        $csv = "User ID,Timestamp,Direction,Device ID\n";
        foreach ($records as $record) {
            $csv .= sprintf(
                "%s,%s,%s,%s\n",
                $record->user_id,
                $record->timestamp,
                $record->direction ?? '',
                $record->device_id ?? ''
            );
        }

        Storage::put($filename, $csv);
        $this->line("  ✓ Exported logs: {$records->count()} records");

        return $filename;
    }

    private function exportManualRequests($startDate, $endDate, $monthStr, $backupDir)
    {
        $filename = "{$backupDir}/manual_requests_{$monthStr}.csv";

        $records = ManualAttendanceRequest::whereBetween('date', [$startDate, $endDate])
            ->with('user')
            ->orderBy('date')
            ->get();

        $csv = "User ID,User Name,Date,Start Time,End Time,Duration,Reason,Status,Created At,Approved At\n";
        foreach ($records as $record) {
            $csv .= sprintf(
                "%s,%s,%s,%s,%s,%s,%s,%s,%s,%s\n",
                $record->user_id,
                $this->escapeCsv($record->user->full_name ?? 'Unknown'),
                $record->date,
                $record->start_time ?? '',
                $record->end_time ?? '',
                $record->duration ?? '',
                $this->escapeCsv($record->reason ?? ''),
                $record->status,
                $record->created_at,
                $record->approved_at ?? ''
            );
        }

        Storage::put($filename, $csv);
        $this->line("  ✓ Exported manual requests: {$records->count()} records");

        return $filename;
    }

    private function exportLeaves($startDate, $endDate, $monthStr, $backupDir)
    {
        $filename = "{$backupDir}/leaves_{$monthStr}.csv";

        $records = Leave::where(function ($query) use ($startDate, $endDate) {
            $query->whereBetween('start_date', [$startDate, $endDate])
                ->orWhereBetween('end_date', [$startDate, $endDate]);
        })
            ->with('user')
            ->orderBy('start_date')
            ->get();

        $csv = "User ID,User Name,Start Date,End Date,Days,Type,Reason,Status,Created At,Approved At\n";
        foreach ($records as $record) {
            $csv .= sprintf(
                "%s,%s,%s,%s,%s,%s,%s,%s,%s,%s\n",
                $record->user_id,
                $this->escapeCsv($record->user->full_name ?? 'Unknown'),
                $record->start_date,
                $record->end_date,
                $record->days ?? '',
                $record->leave_type ?? '',
                $this->escapeCsv($record->reason ?? ''),
                $record->status,
                $record->created_at,
                $record->approved_at ?? ''
            );
        }

        Storage::put($filename, $csv);
        $this->line("  ✓ Exported leaves: {$records->count()} records");

        return $filename;
    }

    private function createZipArchive($files, $zipFilename)
    {
        $zipPath = Storage::path($zipFilename);
        $zip = new ZipArchive();

        if ($zip->open($zipPath, ZipArchive::CREATE | ZipArchive::OVERWRITE) !== true) {
            $this->error("Failed to create ZIP archive");
            return;
        }

        foreach ($files as $file) {
            $filePath = Storage::path($file);
            $zip->addFile($filePath, basename($file));
        }

        $zip->close();
    }

    private function escapeCsv($value)
    {
        // Escape quotes and wrap in quotes if contains comma, newline, or quote
        $value = str_replace('"', '""', $value);
        if (strpos($value, ',') !== false || strpos($value, "\n") !== false || strpos($value, '"') !== false) {
            return '"' . $value . '"';
        }
        return $value;
    }

    private function formatBytes($bytes)
    {
        if ($bytes >= 1073741824) {
            return number_format($bytes / 1073741824, 2) . ' GB';
        } elseif ($bytes >= 1048576) {
            return number_format($bytes / 1048576, 2) . ' MB';
        } elseif ($bytes >= 1024) {
            return number_format($bytes / 1024, 2) . ' KB';
        }
        return $bytes . ' bytes';
    }
}
