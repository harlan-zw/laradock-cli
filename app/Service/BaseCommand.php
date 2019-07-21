<?php

namespace Laradock\Service;

use Spatie\Emoji\Emoji;
use LaravelZero\Framework\Commands\Command;

class BaseCommand extends Command
{
    public function warn($string, $verbosity = null)
    {
        parent::warn(Emoji::warning().' '.$string, $verbosity);
    }

    public function question($string, $verbosity = null)
    {
        parent::question(Emoji::questionMark().' '.$string, $verbosity);
    }

    public function confirm($question, $default = false)
    {
        return parent::confirm(Emoji::questionMark().' '.$question, $default);
    }

    public function confirmContinue($question, $default = false)
    {
        return parent::confirm(Emoji::questionMark().' '.$question.'. Would you like to continue?', $default);
    }

    public function success($line)
    {
        parent::info(Emoji::heavyCheckMark().' '.$line);
    }

    public function bigSuccess($line)
    {
        parent::info(Emoji::confettiBall().' '.$line);
    }

    public function info($line, $verosity = null)
    {
        parent::line(Emoji::notebook().' '.$line, $verosity);
    }

    public function title(string $title): Command
    {
        parent::getOutput()->title($title);

        return $this;
    }

    public function br()
    {
        parent::line('');
    }

    public function hint($hint)
    {
        parent::line(Emoji::exclamationQuestionMark().'\e[36m'.$hint);
    }
}
