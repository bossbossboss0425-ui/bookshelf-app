<?php

namespace App\Console\Commands;

use App\Models\ReadingPlan;
use App\Notifications\ReadingPlanReminderNotification;
use Illuminate\Console\Command;

class SendReadingPlanReminders extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:send-reading-plan-reminders';

    /**
     * The console command description.
     */
    protected $description = '読書計画の期限更新とリマインダー通知を送信する';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $today = now()->startOfDay();

        // リマインダー通知の対象となる計画を取得
        $readingPlans = ReadingPlan::query()
            ->where('status', 'in_progress')
            ->with('user', 'book')
            ->get();

        foreach ($readingPlans as $readingPlan) {
            $targetDate = $readingPlan->target_date->copy()->startOfDay();

            if ($today->equalTo($targetDate->copy()->subDays(3))) {
                $alreadySent = $readingPlan->user->notifications()
                    ->where('type', ReadingPlanReminderNotification::class)
                    ->where('data->timing', 'three_days_before')
                    ->where('data->reading_plan_id', $readingPlan->id)
                    ->exists();

                if (! $alreadySent) {
                    $readingPlan->user->notify(
                        new ReadingPlanReminderNotification(
                            'three_days_before',
                            '読書予定日の3日前です',
                            "「{$readingPlan->book->title}」の読書予定日まであと3日です。",
                            $readingPlan->id
                        )
                    );
                }
            }

            if ($today->equalTo($targetDate)) {
                $alreadySent = $readingPlan->user->notifications()
                    ->where('type', ReadingPlanReminderNotification::class)
                    ->where('data->timing', 'on_due_date')
                    ->where('data->reading_plan_id', $readingPlan->id)
                    ->exists();

                if (! $alreadySent) {
                    $readingPlan->user->notify(
                        new ReadingPlanReminderNotification(
                            'on_due_date',
                            '読書予定日です',
                            "「{$readingPlan->book->title}」の読書予定日です。",
                            $readingPlan->id
                        )
                    );
                }
            }

            if ($today->equalTo($targetDate->copy()->addDays(3))) {
                $alreadySent = $readingPlan->user->notifications()
                    ->where('type', ReadingPlanReminderNotification::class)
                    ->where('data->timing', 'three_days_after')
                    ->where('data->reading_plan_id', $readingPlan->id)
                    ->exists();

                if (! $alreadySent) {
                    $readingPlan->user->notify(
                        new ReadingPlanReminderNotification(
                            'three_days_after',
                            '読書予定日から3日経過しました',
                            "「{$readingPlan->book->title}」の読書予定日から3日経過しました。",
                            $readingPlan->id
                        )
                    );
                }
            }
        }

        // 期限を過ぎた未完了の計画を期限切れにする
        ReadingPlan::query()
            ->where('status', 'in_progress')
            ->whereDate('target_date', '<', $today)
            ->update([
                'status' => 'expired',
            ]);

        return self::SUCCESS;
    }
}
