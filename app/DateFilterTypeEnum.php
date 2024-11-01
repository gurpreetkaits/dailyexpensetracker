<?php

namespace App;

enum DateFilterTypeEnum: string
{
    case WEEK = 'week';
    case MONTH = 'month';
    case DAY = 'day';
    case CUSTOM = 'custom';
}
