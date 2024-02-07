<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;

use App\Mail\DemoEmail;
use App\Mail\DemoMail;
use Filament\Models\Contracts\FilamentUser;
use Filament\Models\Contracts\HasAvatar;
use Filament\Models\Contracts\HasName;
use Filament\Notifications\Notification;
use Filament\Panel;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Facades\Mail;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable implements FilamentUser, HasAvatar, HasName
{
  use HasApiTokens, HasFactory, Notifiable;

  /**
   * The attributes that are mass assignable.
   *
   * @var array<int, string>
   */
  protected $fillable = [
    'name',
    'email',
    'avatar',
    'phone',
    'is_admin',
    'password',
  ];

  /**
   * The attributes that should be hidden for serialization.
   *
   * @var array<int, string>
   */
  protected $hidden = [
    'password',
    'remember_token',
  ];

  /**
   * The attributes that should be cast.
   *
   * @var array<string, string>
   */
  protected $casts = [
    'email_verified_at' => 'datetime',
    'password' => 'hashed',
  ];

  public function posts()
  {
    return $this->hasMany(Post::class);
  }

  public function comments()
  {
    return $this->hasMany(Comment::class);
  }

  public function replies()
  {
    return $this->hasMany(Reply::class);
  }

  public function canAccessPanel(Panel $panel): bool
  {
    return (bool)$this->is_admin;
  }

  public function getFilamentAvatarUrl(): ?string
  {
    return $this->avatar;
  }

  public function getFilamentName(): string
  {
    return $this->name;
  }

  public function sendEmail(array $data)
  {
    try {
      Mail::to($this->email)->send(new DemoMail($data, $this));
      Notification::make()
        ->success()
        ->duration(3000)
        ->title('Email enviado para ' . $this->name)
        ->body('Email enviado para usuário')
        ->send();
    } catch (\Throwable $th) {
      Notification::make()
        ->danger()
        ->duration(3000)
        ->title('Erro ao enviar email')
        ->body('Erro ao enviar email para usuário')
        ->send();
    }
  }
}
