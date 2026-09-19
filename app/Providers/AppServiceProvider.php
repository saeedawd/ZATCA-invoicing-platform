<?php

namespace App\Providers;

use App\Listeners\SendWelcomeUserEmail;
use Illuminate\Auth\Events\Registered;
use Illuminate\Auth\Notifications\ResetPassword;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Lang;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        //
    }

    public function boot(): void
    {
        Model::preventLazyLoading(! $this->app->isProduction());

        Event::listen(Registered::class, SendWelcomeUserEmail::class);

        ResetPassword::toMailUsing(function (object $notifiable, string $token) {
            $url = url(route('password.reset', [
                'token' => $token,
                'email' => $notifiable->getEmailForPasswordReset(),
            ], false));

            $expire = (int) config('auth.passwords.'.config('auth.defaults.passwords').'.expire', 60);

            return (new MailMessage)
                ->subject('إعادة تعيين كلمة المرور — فواتير زاتكا')
                ->greeting('مرحباً '.($notifiable->name ?? ''))
                ->line('استلمنا طلباً لإعادة تعيين كلمة المرور الخاصة بحسابك.')
                ->action('تعيين كلمة مرور جديدة', $url)
                ->line(Lang::get('ينتهي صلاحية هذا الرابط خلال :count دقيقة.', ['count' => $expire]))
                ->line('إذا لم تطلب إعادة التعيين، يمكنك تجاهل هذه الرسالة بأمان.')
                ->salutation('فريق فواتير زاتكا');
        });
    }
}
