<?php

namespace Capco\AppBundle\Behat;

use Alex\MailCatcher\Behat\MailCatcherContext as Base;

class MailCatcherContext extends Base
{
    /**
     * @Then email should match snapshot :file
     */
    public function emailContentShouldMatch($file, $writeSnapshot = true)
    {
        $message = $this->getCurrentMessage();

        if (!$message->isMultipart()) {
            $content = $message->getContent();
        } elseif ($message->hasPart('text/html')) {
            $content = $this->getCrawler($message)->text();
        } elseif ($message->hasPart('text/plain')) {
            $content = $message->getPart('text/plain')->getContent();
        } else {
            throw new \RuntimeException(sprintf('Unable to read mail'));
        }

        if ($writeSnapshot) {
            try {
                $newSnapshot = fopen(__DIR__ . '/snapshots/' . $file, 'wb');
                fwrite($newSnapshot, $content);
                fclose($newSnapshot);
            } catch (\Exception $e) {
                throw $e;
            }
            sprintf('Snapshot written for mail "%s", you can now relaunch the testsuite.', $file);

            return false;
        }

        try {
            $text = file_get_contents(__DIR__ . '/snapshots/' . $file);
        } catch (\Exception $e) {
            throw $e;
        }

        if (false === strpos($content, $text)) {
            throw new \InvalidArgumentException(
                sprintf(
                    "Unable to find text \"%s\" in current message:\n%s, if you want to updateSnapshot, pass writeSnapshot to true",
                    $text,
                    $message->getContent()
                )
            );
        }
    }

    /**
     * @param Message $message
     *
     * @return Crawler
     */
    private function getCrawler(Message $message)
    {
        if (!class_exists('Symfony\Component\DomCrawler\Crawler')) {
            throw new \RuntimeException(
                'Can\'t crawl HTML: Symfony DomCrawler component is missing from autoloading.'
            );
        }

        return new Crawler($message->getPart('text/html')->getContent());
    }

    /**
     * @return Message|null
     */
    private function getCurrentMessage()
    {
        if (null === $this->currentMessage) {
            throw new \RuntimeException('No message selected');
        }

        return $this->currentMessage;
    }
}
