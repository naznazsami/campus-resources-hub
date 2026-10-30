<?php

namespace App\Enums;

enum ResourceType: string
{
    case Notes = 'notes';
    case Slides = 'slides';
    case PastPaper = 'past_paper';
}
