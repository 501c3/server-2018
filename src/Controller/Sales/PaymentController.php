<?php
/**
 * Copyright (c) 2018. Mark Garber
 * This work, including the code samples, is licensed under a Creative Commons BY-SA 3.0 license.
 */

/**
 * Created by PhpStorm.
 * User: mgarber
 * Date: 4/13/18
 * Time: 7:30 PM
 */

namespace App\Controller\Sales;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;

class PaymentController extends Controller
{
    /**
     * @Route("/sales/payment", name="sales_payment")
     */
    public function index()
    {
        return $this->render('sales/payment.html.twig', [
            'controller_name' => 'PaymentController',
        ]);
    }
}
