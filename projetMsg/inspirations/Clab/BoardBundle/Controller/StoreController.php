<?php

namespace Clab\BoardBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use JMS\SecurityExtraBundle\Annotation\Secure;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Validator\Constraints\File;
use Clab\BoardBundle\Form\Type\Store\StoreProfileType;
use Clab\BoardBundle\Form\Type\Store\StorePreorderType;
use Clab\BoardBundle\Form\Type\Store\StoreSettingsOrderType;
use Clab\BoardBundle\Form\Type\Product\ExtraMakingTimeType;

class StoreController extends Controller
{
    /**
     * @Secure(roles="ROLE_MANAGER_2")
     */
    public function profileAction($contextPk, Request $request)
    {
        $em = $this->getDoctrine()->getManager();
        $this->get('board.helper')->initContext('restaurant', $contextPk);

        $categories = $this->get('clab_taxonomy.manager')
            ->getTermsByVocabulary('categories-restaurants');

        $extraCategories = $this->get('clab_taxonomy.manager')
            ->getTermsByVocabulary('categories-extra');

        $form = $this->createForm(new StoreProfileType(array('categories' => $categories, 'extraCategories' => $extraCategories, 'isMobile' => $this->get('board.helper')->getProxy()->isMobile())), $this->get('board.helper')->getProxy());

        if ($form->handleRequest($request)->isValid()) {
            $em->flush();
            $this->get('session')->getFlashBag()->add('success', 'Les informations ont bien été sauvegardées');

            if ($this->get('board.helper')->getProxy()->getSocialProfile() && $this->get('board.helper')->getProxy()->getSocialProfile()->getFacebookSynch()) {
                $this->get('clab_board.facebook_manager')->updateFacebookInfos($this->get('board.helper')->getProxy());
            }

            return $this->redirectToRoute('board_restaurant_profile', array('contextPk' => $contextPk));
        } elseif ($request->isMethod('POST') && !$form->isValid()) {
            $this->get('session')->getFlashBag()->add('error', 'Le formulaire contient des erreurs');
        }
        $chainstore = $this->getDoctrine()->getRepository('ClabBoardBundle:Client')->find(1);
        $this->get('board.helper')->addParam('chainstore', $chainstore);
        $this->get('board.helper')->addParam('form', $form->createView());

        return $this->render('ClabBoardBundle:Store:profile.html.twig', $this->get('board.helper')->getParams());
    }

    /**
     * @Secure(roles="ROLE_MANAGER_2")
     */
    public function preorderAction($contextPk, Request $request)
    {
        $em = $this->getDoctrine()->getManager();
        $this->get('board.helper')->initContext('restaurant', $contextPk);

        if (!$this->get('app_admin.subscription_manager')->hasAccess($this->get('board.helper')->getProxy(), 'preorder')) {
            return $this->redirectToRoute('board_feature_showcase', array('feature' => 'preorder', 'contextPk' => $contextPk));
        }

        $timesheetManager = $this->get('app_restaurant.timesheet_manager');
        $weekDayPlanning = $timesheetManager->getWeekDayPlanning($this->get('board.helper')->getProxy(), 'preorder');

        $form = $this->createForm(new StorePreorderType(array('weekDays' => $weekDayPlanning)), $this->get('board.helper')->getProxy());

        if ($form->handleRequest($request)->isValid()) {
            for ($i = 1; $i < 8; ++$i) {
                $day = $form->get('is_weekday_'.$i)->getData();

                if ($day) {
                    $timesheets = $form->get('timesheets_'.$i)->getData();
                    foreach ($timesheets as $timesheet) {
                        $timesheet->setRestaurant($this->get('board.helper')->getProxy());
                        $timesheet->setDays(array($i));
                        $em->persist($timesheet);
                        unset($weekDayPlanning[$i][$timesheet->getId()]);
                    }
                }

                foreach ($weekDayPlanning[$i] as $timesheet) {
                    $em->remove($timesheet);
                }
            }

            $em->flush();

            return $this->redirectToRoute('board_restaurant_preorder', array('contextPk' => $contextPk));
        }

        $this->get('board.helper')->addParam('form', $form->createView());

        return $this->render('ClabBoardBundle:Store:preorder.html.twig', $this->get('board.helper')->getParams());
    }

    /**
     * @Secure(roles="ROLE_MANAGER_2")
     */
    public function settingsOrderAction(Request $request, $contextPk)
    {
        $em = $this->getDoctrine()->getManager();
        $this->get('board.helper')->initContext('restaurant', $contextPk);

        $products = $this->get('app_restaurant.product_manager')->getForRestaurant($this->get('board.helper')->getProxy());
        foreach ($products as $key => $product) {
            if (!$product->getExtraMakingTime()) {
                unset($products[$key]);
            }
        }
        $types = $this->get('board.helper')->getProxy()->getOrderTypes()->toArray();

        $orderTypes = array();
        if (!empty($types)) {
            foreach ($types as $type) {
                $orderTypes = $type->getSlug();
            }
        }

        $form = $this->createForm(new StoreSettingsOrderType(), $this->get('board.helper')->getProxy());

        if ($form->handleRequest($request)->isValid()) {
            $em->flush();

            return $this->redirectToRoute('board_restaurant_settings_orders', array('contextPk' => $contextPk));
        }

        $this->get('board.helper')->addParams(array(
            'form' => $form->createView(),
            'products' => $products,
            'orderTypes' => $orderTypes,
            'restaurant' => $this->get('board.helper')->getProxy(),
        ));

        return $this->render('ClabBoardBundle:Store:settings-order.html.twig', $this->get('board.helper')->getParams());
    }

    /**
     * @Secure(roles="ROLE_MANAGER_2")
     */
    public function extraMakingTimeAction($contextPk, Request $request)
    {
        $em = $this->getDoctrine()->getManager();
        $this->get('board.helper')->initContext('restaurant', $contextPk);

        $products = $this->get('app_restaurant.product_manager')->getForRestaurant($this->get('board.helper')->getProxy());

        $form = $this->createFormBuilder()
            ->add('products', 'collection', array(
                'type' => new ExtraMakingTimeType(),
                'data' => $products,
                'required' => false,
            ))
        ->getForm();

        if ($form->handleRequest($request)->isValid()) {
            $em->flush();

            return $this->redirectToRoute('board_restaurant_settings_orders', array('contextPk' => $contextPk));
        }

        $this->get('board.helper')->addParam('form', $form->createView());

        return $this->render('ClabBoardBundle:Store:productsExtraMakingTime.html.twig', $this->get('board.helper')->getParams());
    }

    /**
     * @Secure(roles="ROLE_MANAGER_2")
     */
    public function importCatalogAction($contextPk, Request $request)
    {
        set_time_limit(300);
        $chainstore = $this->getDoctrine()->getRepository('ClabBoardBundle:Client')->findOneBy(array('slug' => $contextPk));

        if (!empty($chainstore) && !is_null($chainstore)) {
            $this->get('board.helper')->initContext('client', $contextPk);
            $context = 'client';
        } else {
            $this->get('board.helper')->initContext('restaurant', $contextPk);
            $context = 'restaurant';
        }

        $form = $this->createFormBuilder()
            ->add('file', 'file', array('label' => 'Fichier d\'import', 'constraints' => array(
                new File(array(
                    'mimeTypes' => array(
                        //'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
                        //@todo format
                    ),
                    'mimeTypesMessage' => 'Choisissez un fichier Excel valide',
                )),
            )))
            ->getForm();

        if ($form->handleRequest($request)->isValid()) {
            if ($file = $form->get('file')->getData()) {
                $phpExcelObject = $this->get('phpexcel')->createPHPExcelObject();

                try {
                    $inputFileType = \PHPExcel_IOFactory::identify($file);
                    $objReader = \PHPExcel_IOFactory::createReader($inputFileType);
                    $objPHPExcel = $objReader->load($file);

                    $this->get('clab_board.catalog_manager')->createFromExcel($context, $this->get('board.helper')->getProxy(), $objPHPExcel);
                } catch (\Exception $e) {
                    $this->get('session')->getFlashBag()->add('notice', 'Il semble y avoir une erreur avec votre
                    fichier, avez-vous bien suivi nos indications ?'.$e->getMessage());

                    return $this->redirectToRoute('board_catalog_import', array('contextPk' => $contextPk));
                }

                $this->get('session')->getFlashBag()->add('success', 'Votre carte a bien été importée');

                return $this->redirectToRoute('board_category_library', array('context' => $context, 'contextPk' => $contextPk));
            }

            return $this->redirectToRoute('board_catalog_import', array('contextPk' => $contextPk));
        }

        $this->get('board.helper')->addParam('form', $form->createView());

        return $this->render('ClabBoardBundle:Store:importCatalog.html.twig', $this->get('board.helper')->getParams());
    }

    /**
     * @Secure(roles="ROLE_MANAGER_2")
     */
    public function importRestaurantAction($contextPk, Request $request)
    {
        set_time_limit(-1);
        $chainstore = $this->getDoctrine()->getRepository('ClabBoardBundle:Client')->findOneBy(array('slug' => $contextPk));

        if (!empty($chainstore) && !is_null($chainstore)) {
            $this->get('board.helper')->initContext('client', $contextPk);
            $context = 'client';

            $form = $this->createFormBuilder()
                ->add('file', 'file', array(
                    'label' => 'Fichier d\'import',
                    'constraints' => array(
                        new File(array(
                            'mimeTypes' => array(
                                //'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
                                //@todo format
                            ),
                            'mimeTypesMessage' => 'Choisissez un fichier Excel valide',
                        )),
                    ),
                ))
                ->getForm();

            if ($form->handleRequest($request)->isValid()) {
                if ($file = $form->get('file')->getData()) {
                    $inputFileType = \PHPExcel_IOFactory::identify($file);
                    $objReader = \PHPExcel_IOFactory::createReader($inputFileType);
                    $objPHPExcel = $objReader->load($file);

                    $phpExcelObject = $this->get('phpexcel')->createPHPExcelObject();
                    $this->get('clab_board.restaurant_manager')->createRestaurantFromExcel($context,
                        $this->get('board.helper')->getProxy(), $objPHPExcel);
                    try {


                    } catch (\Exception $e) {
                        $this->get('session')->getFlashBag()->add('notice', 'Il semble y avoir une erreur avec votre
                    fichier, avez-vous bien suivi nos indications ?'.$e->getMessage());

                        return $this->redirectToRoute('board_restaurant_import', array('contextPk' => $contextPk));
                    }

                    $this->get('session')->getFlashBag()->add('success', 'Vos restaurant ont bien été importée');

                    return $this->redirectToRoute('board_dashboard',
                        array('context' => $context, 'contextPk' => $contextPk));
                }

                return $this->redirectToRoute('board_restaurant_import', array('contextPk' => $contextPk));
            }

            $this->get('board.helper')->addParam('form', $form->createView());

            return $this->render('ClabBoardBundle:Store:importRestaurant.html.twig',
                $this->get('board.helper')->getParams());
        } else {
            $context = 'restaurant';

            return $this->redirectToRoute('board_dashboard', array('context' => $context, 'contextPk' => $contextPk));
        }
    }
}
