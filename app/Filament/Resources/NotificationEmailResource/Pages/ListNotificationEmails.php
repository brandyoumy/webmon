<?php

namespace App\Filament\Resources\NotificationEmailResource\Pages;

use App\Filament\Resources\NotificationEmailResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListNotificationEmails extends ListRecords
{
    protected static string $resource = NotificationEmailResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\Action::make('send_test_email')
                ->label('Send Test Email')
                ->icon('heroicon-o-paper-airplane')
                ->color('info')
                ->form([
                    \Filament\Forms\Components\TextInput::make('email')
                        ->label('Recipient Email')
                        ->email()
                        ->required()
                        ->default(fn () => auth()->user()?->email),
                ])
                ->action(function (array $data) {
                    try {
                        \Illuminate\Support\Facades\Mail::raw('This is a test email sent from your BrandYou Webmon Website Monitoring System to verify that your mail configuration (SMTP) is working correctly.', function ($message) use ($data) {
                            $message->to($data['email'])
                                ->subject('Webmon Test Email');
                        });

                        \Filament\Notifications\Notification::make()
                            ->title('Test Email Sent')
                            ->body('The test email has been successfully sent to ' . $data['email'])
                            ->success()
                            ->send();
                    } catch (\Exception $e) {
                        \Filament\Notifications\Notification::make()
                            ->title('Mail Sending Failed')
                            ->body('Error: ' . $e->getMessage())
                            ->danger()
                            ->persistent()
                            ->send();
                    }
                }),
            Actions\CreateAction::make()->label('Add Notification Email'),
        ];
    }
}
