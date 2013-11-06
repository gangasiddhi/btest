<?php

interface IPGCart {
	/**
     * @return  array Bank response
     */
    public function CheckoutServer2Server($timeout, $die);
}

?>