<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class EmailWeeklySummaryNotificaton extends Notification implements ShouldQueue
{
    use Queueable;

    protected  $expenseTotal;
    protected  $incomeTotal;
    /**
     * Create a new notification instance.
     */
    public function __construct(array $totals)
    {
        $this->expenseTotal = $totals['totals']['expense'] ?? 0;
        $this->incomeTotal = $totals['totals']['income'] ?? 0;
    }

    /**
     * Get the notification's delivery channels.
     *
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    /**
     * Get the mail representation of the notification.
     */
    public function toMail(object $notifiable): MailMessage
    {
        $startOfLastWeek = now()->startOfWeek()->subWeek()->toDateString();
        $endOfLastWeek = now()->startOfWeek()->subDay()->toDateString();

        return (new MailMessage)
            ->subject('Weekly Summary from ' . $startOfLastWeek . ' to ' . $endOfLastWeek)
            ->markdown('emails.weekly-summary',[
                'greeting' => 'Hi ' . $notifiable->name,
                'expenseTotal' => $this->expenseTotal,
                'incomeTotal' => $this->incomeTotal,
                'startOfLastWeek' => $startOfLastWeek,
                'endOfLastWeek' => $endOfLastWeek
            ]);
    }

    /**
     * Get the array representation of the notification.
     *
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        return [
            //
        ];
    }
}
