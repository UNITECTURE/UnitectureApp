<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Carbon\Carbon;
use App\Models\Project;
use App\Models\Task;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\DB;

class ArchiveCompletedProjects extends Command
{
    protected $signature = 'projects:archive {--months=6 : Archive projects completed X months ago} {--dry-run : Show what would be archived}';
    protected $description = 'Archive projects that have been completed for 6+ months';

    public function handle()
    {
        $months = $this->option('months');
        $dryRun = $this->option('dry-run');
        $cutoffDate = Carbon::now()->subMonths($months);

        $this->info("=== Project Archival Tool ===");
        $this->info("Archiving projects completed before: {$cutoffDate->toDateString()}");
        $this->info("Mode: " . ($dryRun ? "DRY RUN" : "LIVE"));
        $this->newLine();

        // Find completed projects older than cutoff
        $projects = Project::where('status', 'completed')
            ->where('end_date', '<', $cutoffDate)
            ->whereNull('archived_at') // Only non-archived projects
            ->with(['creator'])
            ->get();

        if ($projects->isEmpty()) {
            $this->info("No projects to archive!");
            return 0;
        }

        $this->info("Found {$projects->count()} projects to archive:");
        $this->newLine();

        $tableData = [];
        foreach ($projects as $project) {
            $taskCount = Task::where('project_id', $project->id)->count();
            $tableData[] = [
                $project->id,
                $project->name,
                $project->end_date,
                $taskCount,
                $project->creator->full_name ?? 'Unknown'
            ];
        }

        $this->table(
            ['ID', 'Project Name', 'Completed', 'Tasks', 'Created By'],
            $tableData
        );

        if ($dryRun) {
            $this->warn("\n✓ DRY RUN COMPLETE - No data was modified");
            return 0;
        }

        if (!$this->confirm("Archive these {$projects->count()} projects?", false)) {
            $this->info("Archival cancelled");
            return 0;
        }

        // Create archive directory
        $archiveDir = "archive/projects/" . Carbon::now()->format('Y-m');
        Storage::makeDirectory($archiveDir);

        $this->info("\nArchiving projects...");
        $bar = $this->output->createProgressBar($projects->count());

        foreach ($projects as $project) {
            $this->archiveProject($project, $archiveDir);
            $bar->advance();
        }

        $bar->finish();
        $this->newLine(2);

        $this->info("✓ Archival complete!");
        $this->info("Archive location: storage/app/{$archiveDir}");
        $this->info("Projects archived: {$projects->count()}");

        return 0;
    }

    private function archiveProject($project, $archiveDir)
    {
        // Get all related data
        $tasks = Task::where('project_id', $project->id)
            ->with(['assignedTo', 'comments.user'])
            ->get();

        // Create project archive data
        $archiveData = [
            'project' => [
                'id' => $project->id,
                'name' => $project->name,
                'code' => $project->project_code,
                'custom_id' => $project->project_custom_id,
                'department' => $project->department,
                'location' => $project->location,
                'description' => $project->description,
                'start_date' => $project->start_date,
                'end_date' => $project->end_date,
                'status' => $project->status,
                'created_by' => $project->creator->full_name ?? 'Unknown',
                'created_at' => $project->created_at,
            ],
            'tasks' => [],
            'statistics' => [
                'total_tasks' => $tasks->count(),
                'completed_tasks' => $tasks->where('status', 'completed')->count(),
                'archived_at' => Carbon::now()->toDateTimeString(),
            ]
        ];

        // Add tasks with comments
        foreach ($tasks as $task) {
            $taskData = [
                'id' => $task->id,
                'title' => $task->title,
                'description' => $task->description,
                'status' => $task->status,
                'stage' => $task->stage,
                'due_date' => $task->due_date,
                'assigned_to' => $task->assignedTo->full_name ?? 'Unassigned',
                'created_at' => $task->created_at,
                'completed_at' => $task->completed_at,
                'comments' => []
            ];

            foreach ($task->comments as $comment) {
                $taskData['comments'][] = [
                    'user' => $comment->user->full_name ?? 'Unknown',
                    'comment' => $comment->comment,
                    'created_at' => $comment->created_at,
                ];
            }

            $archiveData['tasks'][] = $taskData;
        }

        // Save as JSON
        $filename = "{$archiveDir}/project_{$project->id}_{$project->project_code}.json";
        Storage::put($filename, json_encode($archiveData, JSON_PRETTY_PRINT));

        // Also create a readable HTML report
        $htmlFilename = "{$archiveDir}/project_{$project->id}_{$project->project_code}.html";
        Storage::put($htmlFilename, $this->generateHtmlReport($archiveData));

        // Mark project as archived in database
        DB::table('projects')
            ->where('id', $project->id)
            ->update([
                'archived_at' => Carbon::now(),
                'updated_at' => Carbon::now()
            ]);
    }

    private function generateHtmlReport($data)
    {
        $project = $data['project'];
        $tasks = $data['tasks'];
        $stats = $data['statistics'];

        $html = <<<HTML
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>{$project['name']} - Project Archive</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 20px; background: #f5f5f5; }
        .container { max-width: 1200px; margin: 0 auto; background: white; padding: 30px; border-radius: 8px; box-shadow: 0 2px 4px rgba(0,0,0,0.1); }
        h1 { color: #333; border-bottom: 3px solid #4CAF50; padding-bottom: 10px; }
        h2 { color: #555; margin-top: 30px; }
        .info-grid { display: grid; grid-template-columns: repeat(2, 1fr); gap: 15px; margin: 20px 0; }
        .info-item { padding: 10px; background: #f9f9f9; border-left: 3px solid #4CAF50; }
        .info-label { font-weight: bold; color: #666; font-size: 12px; text-transform: uppercase; }
        .info-value { color: #333; margin-top: 5px; }
        .stats { background: #e8f5e9; padding: 15px; border-radius: 5px; margin: 20px 0; }
        .task { background: #fafafa; padding: 15px; margin: 15px 0; border-radius: 5px; border-left: 4px solid #2196F3; }
        .task-title { font-weight: bold; color: #333; font-size: 16px; }
        .task-meta { color: #666; font-size: 14px; margin: 5px 0; }
        .comment { background: white; padding: 10px; margin: 10px 0; border-left: 2px solid #ddd; }
        .comment-user { font-weight: bold; color: #555; }
        .comment-date { color: #999; font-size: 12px; }
        .badge { display: inline-block; padding: 3px 8px; border-radius: 3px; font-size: 12px; font-weight: bold; }
        .badge-completed { background: #4CAF50; color: white; }
        .badge-pending { background: #FF9800; color: white; }
    </style>
</head>
<body>
    <div class="container">
        <h1>📁 {$project['name']}</h1>
        
        <div class="info-grid">
            <div class="info-item">
                <div class="info-label">Project Code</div>
                <div class="info-value">{$project['code']}</div>
            </div>
            <div class="info-item">
                <div class="info-label">Department</div>
                <div class="info-value">{$project['department']}</div>
            </div>
            <div class="info-item">
                <div class="info-label">Location</div>
                <div class="info-value">{$project['location']}</div>
            </div>
            <div class="info-item">
                <div class="info-label">Created By</div>
                <div class="info-value">{$project['created_by']}</div>
            </div>
            <div class="info-item">
                <div class="info-label">Start Date</div>
                <div class="info-value">{$project['start_date']}</div>
            </div>
            <div class="info-item">
                <div class="info-label">End Date</div>
                <div class="info-value">{$project['end_date']}</div>
            </div>
        </div>

        <div class="stats">
            <strong>📊 Statistics:</strong><br>
            Total Tasks: {$stats['total_tasks']} | 
            Completed: {$stats['completed_tasks']} | 
            Archived: {$stats['archived_at']}
        </div>

        <p><strong>Description:</strong> {$project['description']}</p>

        <h2>📋 Tasks ({$stats['total_tasks']})</h2>
HTML;

        foreach ($tasks as $task) {
            $statusBadge = $task['status'] === 'completed'
                ? '<span class="badge badge-completed">Completed</span>'
                : '<span class="badge badge-pending">Pending</span>';

            $html .= <<<TASK
        <div class="task">
            <div class="task-title">{$task['title']} {$statusBadge}</div>
            <div class="task-meta">
                Assigned to: {$task['assigned_to']} | 
                Stage: {$task['stage']} | 
                Due: {$task['due_date']}
            </div>
            <p>{$task['description']}</p>
TASK;

            if (!empty($task['comments'])) {
                $html .= "<div style='margin-top: 10px;'><strong>Comments:</strong></div>";
                foreach ($task['comments'] as $comment) {
                    $html .= <<<COMMENT
            <div class="comment">
                <div class="comment-user">{$comment['user']}</div>
                <div class="comment-date">{$comment['created_at']}</div>
                <div>{$comment['comment']}</div>
            </div>
COMMENT;
                }
            }

            $html .= "</div>";
        }

        $html .= <<<HTML
    </div>
</body>
</html>
HTML;

        return $html;
    }
}
