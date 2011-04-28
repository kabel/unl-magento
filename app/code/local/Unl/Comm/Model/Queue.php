<?php

class Unl_Comm_Model_Queue extends Mage_Core_Model_Abstract
{
    const STATUS_NEVER = 0;
    const STATUS_SENDING = 1;
    const STATUS_CANCEL = 2;
    const STATUS_SENT = 3;
    const STATUS_PAUSE = 4;

    /**
     * Recipient collection
     * @var Varien_Data_Collection_Db
     */
    protected $_recipientsCollection = null;

    /**
     * Initialize resource model
     */
    protected function _construct()
    {
        $this->_init('unl_comm/queue');
    }

    /**
     * Return: is this queue newly created or not.
     *
     * @return boolean
     */
    public function isNew()
    {
        return (is_null($this->getQueueStatus()));
    }

    public function isPlain()
    {
        return ($this->getType() == Mage_Core_Model_Template::TYPE_TEXT);
    }

    /**
     * Returns recipient collection for this queue
     *
     * @return Varien_Data_Collection_Db
     */
    public function getRecipientCollection()
    {
        if (is_null($this->_recipientsCollection)) {
            $this->_recipientsCollection = Mage::getResourceModel('unl_comm/recipient_collection')
                ->useQueue($this);
        }

        return $this->_recipientsCollection;
    }

    /**
     * Set $_data['queue_start'] based on string from backend, which based on locale.
     *
     * @param string|null $startAt start date of the mailing queue
     * @return Unl_Comm_Model_Queue
     */
    public function setQueueStartAtByString($startAt)
    {
        if(is_null($startAt) || $startAt == '') {
            $this->setQueueStartAt(null);
        } else {
            $locale = Mage::app()->getLocale();
            $format = $locale->getDateTimeFormat(Mage_Core_Model_Locale::FORMAT_TYPE_MEDIUM);
            $time = $locale->date($startAt, $format)->getTimestamp();
            $this->setQueueStartAt(Mage::getModel('core/date')->gmtDate(null, $time));
        }
        return $this;
     }

    /**
     * Send messages to recipients for this queue
     *
     * @param   int     $count
     * @param   array   $additionalVariables
     * @return Unl_Comm_Model_Queue
     */
    public function sendPerRecipient($count=20, array $additionalVariables=array())
    {
        if($this->getQueueStatus()!=self::STATUS_SENDING && ($this->getQueueStatus()!=self::STATUS_NEVER && $this->getQueueStartAt()) ) {
            return $this;
        }

        if($this->getRecipientCollection()->getSize()==0) {
            return $this->delete();
        }

        // Start Sending
        if ($this->getQueueStatus() != self::STATUS_SENDING) {
            $this->setQueueStatus(self::STATUS_SENDING);
            $this->save();
        }

        $collection = $this->getRecipientCollection()
            ->useOnlyUnsent()
            ->setPageSize($count)
            ->setCurPage(1)
            ->load();

        $sender = $this->getEmailTemplate();
        $sender->setSenderName($this->getMessageSenderName())
            ->setSenderEmail($this->getMessageSenderEmail())
            ->setTemplateSubject($this->getMessageSubject());

        /* @var $item Mage_Customer_Model_Customer */
        foreach($collection->getItems() as $item) {
            $email = $item->getEmail();
            $name = $item->getName();

            $sender->emulateDesign($item->getStoreId());
            $successSend = $sender->send($email, $name, array('customer'=>$item));
            $sender->revertDesign();

            if($successSend) {
                $this->_getResource()->markReceived($this, $item);
            } else {
                Mage::log('Customer communication failure. Please refer to exception.log', Zend_Log::WARN);
            }
        }

        if(count($collection->getItems()) < $count-1 || count($collection->getItems()) == 0) {
            $this->setQueueFinishAt(now());
            $this->setQueueStatus(self::STATUS_SENT);
            $this->save();
        }
        return $this;
    }

    /**
     * Returns an Email_Template instance for this queue
     *
     * @return Mage_Core_Model_Email_Template
     */
    public function getEmailTemplate()
    {
        /* @var $template Mage_Core_Model_Email_Template */
        $template = Mage::getModel('core/email_template');
        $template->setTemplateType($this->getType())
            ->setTemplateText($this->getMessageText())
            ->setTemplateStyles($this->getMessageStyles())
            ->setTemplateFilter(Mage::helper('unl_comm')->getTemplateProcessor());

        return $template;
    }

    /**
     * Add subscribers to queue.
     *
     * @param array $customerIds
     * @return Unl_Comm_Model_Queue
     */
    public function addRecipientsToQueue(array $customerIds)
    {
        $this->_getResource()->addRecipients($this, $customerIds);
        return $this;
    }

    /**
     * Getter for msg type
     *
     * @return int|string
     */
    public function getType()
    {
        return $this->getMessageType();
    }
}
