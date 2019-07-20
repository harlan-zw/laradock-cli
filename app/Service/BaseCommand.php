<?php


namespace Laradock\Service;


use LaravelZero\Framework\Commands\Command;
use Spatie\Emoji\Emoji;

class BaseCommand extends Command  {

    public function warn($string, $verbosity = null) {
        parent::warn(Emoji::warning() . ' ' . $string, $verbosity);
    }

    public function question($string, $verbosity = null) {
        parent::question(Emoji::questionMark() . ' ' . $string, $verbosity);
    }

    public function confirm($question, $default = false) {
        return parent::confirm(Emoji::questionMark() . ' ' . $question, $default);
    }

    public function confirmContinue($question, $default = false) {
        return parent::confirm(Emoji::questionMark() . ' ' . $question . '. Would you like to continue?', $default);
    }


    public function success($line) {
        parent::info(Emoji::heavyCheckMark() . ' ' . $line);
    }

    public function bigSuccess($line) {
        parent::info(Emoji::confettiBall() . ' ' . $line);
    }

    public function info($line, $verosity = null) {
        parent::info(Emoji::notebook() . ' ' . $line, $verosity);
    }

}
