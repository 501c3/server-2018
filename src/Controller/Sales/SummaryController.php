<?php
/**
 * Copyright (c) 2018. Mark Garber
 * This work, including the code samples, is licensed under a Creative Commons BY-SA 3.0 license.
 */

/**
 * Created by PhpStorm.
 * User: mgarber
 * Date: 4/13/18
 * Time: 7:29 PM
 */

namespace App\Controller\Sales;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;

class SummaryController extends Controller
{
    /**
     * @Route("/sales/summary", name="sales_summary")
     */
    public function index()
    {
        return $this->render('sales/summary.html.twig', [
            'controller_name' => 'SummaryController',
        ]);
    }
}
