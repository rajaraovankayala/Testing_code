<?php
namespace base\activinstinct\view;


class OrderEmailView extends \base\common\view\AbstractOrderEmailView {

    public function mapPlainBody(){
        if(is_null($this->order)){
            throw InternalServerErrorException("Can't build email without order");
        }

        $template = $this->getPlainBodyTemplate();
        $this->map('<<COURIER_TRACKING>>', $this->mapCourierTracking($this->order, 'plain'), $template);
        $this->map('<<RECIPIENT_EMAIL>>', $this->order->customerEmail, $template);
        $this->map('<<INVOICE_LINES>>', $this->mapInvoiceLines($this->order->invoice->lines,'plain'), $template);
        return $this->mapGeneric($template);
    }

    public function mapHtmlBody(){
        if(is_null($this->order)){
            throw InternalServerErrorException("Can't build email without order");
        }

        $template = $this->getHtmlBodyTemplate();
        $this->map('<<CUSTOMER_ORDERID>>', $this->mapCourierTracking($this->orderID, 'HTML'), $template);
        $this->map('<<COURIER_TRACKING>>', $this->mapCourierTracking($this->order, 'HTML'), $template);
        $this->map('<<RECIPIENT_EMAIL>>', $this->order->customerEmail, $template);
        $this->map('<<INVOICE_LINES>>', $this->mapInvoiceLines($this->order->invoice->lines), $template);
        return $this->mapGeneric($template);
    }

    protected function mapInvoiceLines($invoiceLines,$type=null) {
        $this->setCurrency('£');
        $template='';
        foreach($invoiceLines as $invoiceLine) {
            $template .= $this->mapInvoiceLine($invoiceLine,$type);
        }
        return $template;
    }

    /**
     * Returns HTML template for invoice line, ensure table column count is same as the HTML email template being used
     * @return string
     */
    protected function getInvoiceLineHTMLTemplate(){
        return
            '<tr>
                <td align="left" valign="top" style="padding:15px;border-top:1px dashed #CCCCCC;" width="50%">
                    <strong><<ITEM_NAME>></strong>
                    <strong><<ITEM_NAME>></strong>
                    <<ITEM_OPTION_SIZE>>
                </td>
                <td align="left" valign="top"  style="padding:15px;border-top:1px dashed #CCCCCC;" ><<ITEM_SKU>></td>
                <td align="center" valign="top"  style="padding:15px;border-top:1px dashed #CCCCCC;" ><<ITEM_QTY>></td>
                <td align="right" valign="top"  style="padding:15px;border-top:1px dashed #CCCCCC;" >
                    <strong>'.$this->getCurrencySymbol().'<<ITEM_PRICE>></strong>
                </td>
            </tr>';
    }

    protected function mapCourierTracking($order,$type=null) {
        $template='';
        if ($order->getCourier() == null) {
            $template = 'Just a quick update... Your order has been dispatched and will be with you shortly.';
            return $template;
        }
        $courierName = $order->getCourier()->getName();
        $trackingReferenceURL = $order->getCourierTrackingURL();
        $template = 'Just a quick update... Your order has now been dispatched';
        if ($trackingReferenceURL !== null) {
            if ($type == 'plain') {
                $template .= ' and can be tracked here - <<COURIER_TRACKING_URL>>';
                $template .= '. Tracking information will be available in the next 24 hours. If there is no tracking information available you may be trying to track your order too early.';
            }
            else {
                $template .= ' and can be tracked <a href="<<COURIER_TRACKING_URL>>" target="_blank">here</a>';
                $template .= '.<br>Tracking information will be available in the next 24 hours. If there is no tracking information available you may be trying to track your order too early.';

            }
            $this->map('<<COURIER_TRACKING_URL>>', $trackingReferenceURL, $template);
        }
        return $template;
    }
}