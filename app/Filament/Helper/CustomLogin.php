<?php
namespace App\Filament\Helper;

use DiogoGPinto\AuthUIEnhancer\Pages\Auth\Concerns\HasCustomLayout;
use Filament\Auth\Pages\Login;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;

class CustomLogin extends Login
{
    use HasCustomLayout;
    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('username')
                ->required(),
                $this->getPasswordFormComponent(),
                $this->getRememberFormComponent()
               
            ]);
    }

    protected function getCredentialsFromFormData(#[SensitiveParameter] array $data): array
    {
        return [
            'username' => $data['username'],
            'password' => $data['password'],
        ];
    }

}
