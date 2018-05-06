<?php
/**
 * Created by PhpStorm.
 * User: mgarber
 * Date: 3/18/18
 * Time: 6:24 PM
 */

namespace App\Subscriber;
use Symfony\Component\Console\Helper\ProgressBar;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

// TODO: Set progress bar to green colo

class CommandStatusSubscriber implements EventSubscriberInterface
{
    /** @var OutputInterface */
    private $cli;

    /** @var \DateTime */
    private $start;

    /** @var  ProgressBar*/
    private $progressBar;

    private $lastProgress;

    public function setOutputInterface(OutputInterface $output)
    {
        $this->cli=$output;
    }

    public function progressBarSetup($totalLines){
        $this->progressBar = new ProgressBar($this->cli, $totalLines);
        $this->progressBar->setBarWidth(60);
        $this->progressBar->clear();
    }

    /**
     * @param StatusEvent $event
     */
    public function onStatusUpdate(StatusEvent $event){
        $status = $event->getStatus();
        $event->stopPropagation();
        if(!$this->cli) return;
        switch($status->getStatus()){
            case Status::COMMENCE:
                $this->start=$status->getTimestamp();
                $date = sprintf($this->start->format('Y-m-d  H:i:s'));
                $this->cli->writeln( "<fg=green>Commencing at $date</>" );
                $this->progressBarSetup( $status->getProgress() );
                $this->lastProgress = 0;
                $this->progressBar->display();
                break;
            case Status::WORKING:
               $this->progressBar->setProgress($status->getProgress());
                break;
            case Status::COMPLETE:
                $this->progressBar->finish();
                $this->cli->writeln("");
                $timestamp=$status->getTimestamp();
                $date = sprintf($timestamp->format('Y-m-d  H:i:s'));
                $completed=sprintf("<fg=green>Completed at %s</>",$date);
                $this->cli->writeln($completed);
                $duration=$this->start->diff($timestamp);
                $duration = sprintf("<fg=green>Duration : %s hours %s minutes %s seconds </>",
                                        $duration->h, $duration->i, $duration->s);
                $this->cli->writeln($duration);
            case Status::ERRORS:
        }
    }

    public static function getSubscribedEvents()
    {
       return [StatusEvent::NAME => ['onStatusUpdate']];
    }

}