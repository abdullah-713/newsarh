<?php

namespace App\Filament\Employee\Pages;

use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Select;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Illuminate\Support\Facades\Hash;

class MyProfile extends Page implements HasForms
{
    use InteractsWithForms;

    protected static ?string $navigationIcon = 'heroicon-o-user-circle';
    
    protected static ?string $navigationLabel = 'ملفي الشخصي';
    
    protected static ?string $title = 'ملفي الشخصي';
    
    protected static string $view = 'filament.employee.pages.my-profile';
    
    protected static ?int $navigationSort = 4;

    public ?array $data = [];
    public ?array $passwordData = [];

    public function mount(): void
    {
        $user = auth()->user();
        
        $this->form->fill([
            'first_name' => $user->first_name,
            'last_name' => $user->last_name,
            'email' => $user->email,
            'phone' => $user->phone,
            'profile_picture' => $user->profile_picture,
        ]);
    }

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                FileUpload::make('profile_picture')
                    ->label('الصورة الشخصية')
                    ->image()
                    ->avatar()
                    ->directory('profile-pictures')
                    ->maxSize(2048),

                TextInput::make('first_name')
                    ->label('الاسم الأول')
                    ->required()
                    ->maxLength(255),

                TextInput::make('last_name')
                    ->label('الاسم الأخير')
                    ->required()
                    ->maxLength(255),

                TextInput::make('email')
                    ->label('البريد الإلكتروني')
                    ->email()
                    ->required()
                    ->maxLength(255),

                TextInput::make('phone')
                    ->label('رقم الهاتف')
                    ->tel()
                    ->maxLength(20),
            ])
            ->statePath('data');
    }

    public function updateProfile()
    {
        $data = $this->form->getState();

        auth()->user()->update($data);

        Notification::make()
            ->success()
            ->title('تم التحديث بنجاح')
            ->body('تم تحديث بياناتك الشخصية')
            ->send();
    }

    public function updatePassword()
    {
        $this->validate([
            'passwordData.current_password' => 'required|current_password',
            'passwordData.password' => 'required|min:8|confirmed',
        ], [
            'passwordData.current_password.required' => 'كلمة المرور الحالية مطلوبة',
            'passwordData.current_password.current_password' => 'كلمة المرور الحالية غير صحيحة',
            'passwordData.password.required' => 'كلمة المرور الجديدة مطلوبة',
            'passwordData.password.min' => 'كلمة المرور يجب أن تكون 8 أحرف على الأقل',
            'passwordData.password.confirmed' => 'كلمة المرور غير متطابقة',
        ]);

        auth()->user()->update([
            'password' => Hash::make($this->passwordData['password'])
        ]);

        $this->passwordData = [];

        Notification::make()
            ->success()
            ->title('تم التحديث بنجاح')
            ->body('تم تحديث كلمة المرور')
            ->send();
    }
}
