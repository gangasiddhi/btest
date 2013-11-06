<?php
interface IPG {

    public static function isOrderCancellable(Order $iOrder);
    public static function isOrderRefundable(Order $iOrder);
}

?>