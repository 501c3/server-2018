<?php
/**
 * Copyright (c) 2018. Mark Garber
 * This work, including the code samples, is licensed under a Creative Commons BY-SA 3.0 license.
 */

/**
 * Created by PhpStorm.
 * User: mgarber
 * Date: 4/13/18
 * Time: 7:28 PM
 */

namespace App\Controller\Sales;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;

class XtrasController extends Controller
{
    /**
     * @Route("/sales/xtras", name="sales_xtras")
     */
    public function index()
    {
        return $this->render('sales/xtras.html.twig', [
            'controller_name' => 'XtrasController',
        ]);
    }
}
