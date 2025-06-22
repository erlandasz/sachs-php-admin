<?php

namespace App;

use Filament\Support\Contracts\HasLabel;

enum SpeakerType: int implements HasLabel
{
    // keynote types
    case Keynote = 5;
    case Interviewee = 3;

    // other types
    case Speaker = 1;
    case Chair = 2;
    case Interviewer = 4;
    case Moderator = 21;
    case DiscussWith = 420;

    public function getLabel(): ?string
    {
        return match ($this) {
            self::Speaker => 'Speaker',
            self::Chair => 'Chair',
            self::Interviewee => 'Interviewee',
            self::Interviewer => 'Interviewer',
            self::Keynote => 'Keynote',
            self::Moderator => 'Moderator',
            self::DiscussWith => 'Discuss With',
        };
    }

    public function getColor(): ?string
    {
        return match ($this) {
            self::Keynote => 'danger',
            self::Interviewee => 'danger',
            default => 'success',
        };
    }
}
