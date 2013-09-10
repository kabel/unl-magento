<?php

class Unl_Comm_Block_Queue_Grid_Renderer_Action extends Mage_Adminhtml_Block_Widget_Grid_Column_Renderer_Action
{
    public function render(Varien_Object $row)
    {
        $actions = array();

        $confirm = 'Are you sure want to ';
        $what = ' this queued message?';

        if ($row->getQueueStatus() == Unl_Comm_Model_Queue::STATUS_NEVER) {
            $actions[] = array(
                'url' => $this->getUrl('*/*/delete', array('id' => $row->getId())),
             	'confirm'	=>	Mage::helper('unl_comm')->__($confirm . 'delete' . $what),
                'caption'	=>	Mage::helper('unl_comm')->__('Delete')
            );
            if(!$row->getQueueStartAt() && $row->getRecipientsTotal()) {
                $actions[] = array(
                    'url' => $this->getUrl('*/*/start', array('id'=>$row->getId())),
                    'caption'	=> Mage::helper('unl_comm')->__('Start')
                );
            }
        } else if ($row->getQueueStatus() == Unl_Comm_Model_Queue::STATUS_SENDING) {
            $actions[] = array(
                    'url' => $this->getUrl('*/*/pause', array('id'=>$row->getId())),
                    'caption'	=>	Mage::helper('unl_comm')->__('Pause')
            );

            $actions[] = array(
                'url'		=>	$this->getUrl('*/*/cancel', array('id'=>$row->getId())),
                'confirm'	=>	Mage::helper('unl_comm')->__($confirm . 'stop' . $what),
                'caption'	=>	Mage::helper('unl_comm')->__('Cancel')
            );


        } else if ($row->getQueueStatus() == Unl_Comm_Model_Queue::STATUS_PAUSE) {

            $actions[] = array(
                'url' => $this->getUrl('*/*/resume', array('id'=>$row->getId())),
                'caption'	=>	Mage::helper('unl_comm')->__('Resume')
            );

        }

        $actions[] = array(
            'url'       =>  $this->getUrl('*/*/preview',array('id'=>$row->getId())),
            'caption'   =>  Mage::helper('unl_comm')->__('Preview'),
            'popup'     =>  true
        );

        $this->getColumn()->setActions($actions);
        return parent::render($row);
    }
}
