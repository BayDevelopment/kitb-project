<?php

namespace App\Enums;

enum LamaranStatus: string
{
  case Submitted = 'submitted';
  case Reviewed = 'reviewed';
  case Interview = 'interview';
  case Accepted = 'accepted';
  case Rejected = 'rejected';

  public function label(): string
  {
    return match ($this) {
      self::Submitted => 'Baru Masuk',
      self::Reviewed => 'Direview',
      self::Interview => 'Interview',
      self::Accepted => 'Diterima',
      self::Rejected => 'Ditolak',
    };
  }
}
