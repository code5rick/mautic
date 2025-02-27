<?php

namespace Mautic\EmailBundle\Tests\MonitoredEmail\Processor;

use Mautic\CoreBundle\Translation\Translator;
use Mautic\EmailBundle\Entity\Email;
use Mautic\EmailBundle\Entity\Stat;
use Mautic\EmailBundle\Entity\StatRepository;
use Mautic\EmailBundle\MonitoredEmail\Message;
use Mautic\EmailBundle\MonitoredEmail\Processor\Bounce;
use Mautic\EmailBundle\MonitoredEmail\Search\ContactFinder;
use Mautic\EmailBundle\MonitoredEmail\Search\Result;
use Mautic\EmailBundle\Tests\MonitoredEmail\Transport\TestTransport;
use Mautic\LeadBundle\Entity\Lead;
use Mautic\LeadBundle\Model\DoNotContact;
use Mautic\LeadBundle\Model\LeadModel;
use Monolog\Logger;
use Symfony\Component\Mailer\Transport\NullTransport;

class BounceTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @testdox Test that the transport interface processes the message appropriately
     *
     * @covers  \Mautic\EmailBundle\MonitoredEmail\Processor\Bounce::process()
     * @covers  \Mautic\EmailBundle\MonitoredEmail\Processor\Bounce::updateStat()
     * @covers  \Mautic\EmailBundle\MonitoredEmail\Search\Result::setStat()
     * @covers  \Mautic\EmailBundle\MonitoredEmail\Search\Result::getStat()
     * @covers  \Mautic\EmailBundle\MonitoredEmail\Search\Result::setContacts()
     * @covers  \Mautic\EmailBundle\MonitoredEmail\Search\Result::getContacts()
     */
    public function testProcessorInterfaceProcessesMessage(): void
    {
        $transport     = new TestTransport();
        $contactFinder = $this->getMockBuilder(ContactFinder::class)
            ->disableOriginalConstructor()
            ->getMock();
        $contactFinder->method('find')
            ->willReturnCallback(
                function ($email, $bounceAddress) {
                    $stat = new Stat();

                    $lead = new Lead();
                    $lead->setEmail($email);
                    $stat->setLead($lead);

                    $email = new Email();
                    $stat->setEmail($email);

                    $result = new Result();
                    $result->setStat($stat);
                    $result->setContacts(
                        [
                            $lead,
                        ]
                    );

                    return $result;
                }
            );

        $statRepo = $this->getMockBuilder(StatRepository::class)
            ->disableOriginalConstructor()
            ->getMock();
        $statRepo->expects($this->once())
            ->method('saveEntity');

        $leadModel = $this->getMockBuilder(LeadModel::class)
            ->disableOriginalConstructor()
            ->getMock();

        $translator = $this->getMockBuilder(Translator::class)
            ->disableOriginalConstructor()
            ->getMock();

        $logger = $this->getMockBuilder(Logger::class)
            ->disableOriginalConstructor()
            ->getMock();

        $doNotContact = $this->createMock(DoNotContact::class);

        $bouncer = new Bounce($transport, $contactFinder, $statRepo, $leadModel, $translator, $logger, $doNotContact);

        $message = new Message();
        $this->assertTrue($bouncer->process($message));
    }

    /**
     * @testdox Test that the message is processed appropriately
     *
     * @covers  \Mautic\EmailBundle\MonitoredEmail\Processor\Bounce::process()
     * @covers  \Mautic\EmailBundle\MonitoredEmail\Processor\Bounce::updateStat()
     * @covers  \Mautic\EmailBundle\MonitoredEmail\Search\Result::setStat()
     * @covers  \Mautic\EmailBundle\MonitoredEmail\Search\Result::getStat()
     * @covers  \Mautic\EmailBundle\MonitoredEmail\Search\Result::setContacts()
     * @covers  \Mautic\EmailBundle\MonitoredEmail\Search\Result::getContacts()
     */
    public function testContactIsFoundFromMessageAndDncRecordAdded(): void
    {
        $transport     = new NullTransport();
        $contactFinder = $this->getMockBuilder(ContactFinder::class)
            ->disableOriginalConstructor()
            ->getMock();
        $contactFinder->method('find')
            ->willReturnCallback(
                function ($email, $bounceAddress) {
                    $stat = new Stat();

                    $lead = new Lead();
                    $lead->setEmail($email);
                    $stat->setLead($lead);

                    $email = new Email();
                    $stat->setEmail($email);

                    $result = new Result();
                    $result->setStat($stat);
                    $result->setContacts(
                        [
                            $lead,
                        ]
                    );

                    return $result;
                }
            );

        $statRepo = $this->getMockBuilder(StatRepository::class)
            ->disableOriginalConstructor()
            ->getMock();
        $statRepo->expects($this->once())
            ->method('saveEntity');

        $leadModel = $this->getMockBuilder(LeadModel::class)
            ->disableOriginalConstructor()
            ->getMock();

        $translator = $this->getMockBuilder(Translator::class)
            ->disableOriginalConstructor()
            ->getMock();

        $logger = $this->getMockBuilder(Logger::class)
            ->disableOriginalConstructor()
            ->getMock();

        $doNotContact = $this->createMock(DoNotContact::class);

        $bouncer = new Bounce($transport, $contactFinder, $statRepo, $leadModel, $translator, $logger, $doNotContact);

        $message            = new Message();
        $message->to        = ['contact+bounce_123abc@test.com' => null];
        $message->dsnReport = <<<'DSN'
Original-Recipient: sdfgsdfg@seznan.cz
Final-Recipient: rfc822;sdfgsdfg@seznan.cz
Action: failed
Status: 5.4.4
Diagnostic-Code: DNS; Host not found
DSN;

        $this->assertTrue($bouncer->process($message));
    }
}
