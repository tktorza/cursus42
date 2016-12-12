<?php
/**
 * Created by PhpStorm.
 * User: lfbarreto
 * Date: 04/04/16
 * Time: 16:55.
 */

namespace Clab\WhiteBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Clab\RestaurantBundle\Entity\Product;
use Clab\RestaurantBundle\Entity\Meal;

class AdditionalSaleController extends Controller
{
    /**
     * @ParamConverter("product", class="ClabRestaurantBundle:Product", options={"repository_method" = "findOneAvailable"})
     */
    public function additionalSaleProductAction(Request $request, Product $product)
    {
        if (!is_null($product->getAdditionalSale())) {
            $additionalSale = $product->getAdditionalSale();

            if (!empty($additionalSale->getAdditionalSaleProducts())) {
                $form = $this->get('clab_board.additional_sale_manager')->getAdditionalSaleForm($additionalSale);

                return $this->render('ClabWhiteBundle:AdditionalSale:additionalSaleForm.html.twig', array(
                    'product' => $product,
                    'formAddSale' => $form->createView(),
                    'restaurant' => $product->getRestaurant(),
                ));
            }
        } else {
            return $this->redirectToRoute('clab_white_order_home', array('slug' => $product->getRestaurant()->getSlug()));
        }
    }

    /**
     * @ParamConverter("meal", class="ClabRestaurantBundle:Meal", options={"repository_method" = "findOneAvailable"})
     */
    public function additionalSaleMealAction(Request $request, Meal $meal)
    {
        if (!is_null($meal->getAdditionalSale())) {
            $additionalSale = $meal->getAdditionalSale();

            if (!empty($additionalSale->getAdditionalSaleProducts())) {
                $form = $this->get('clab_board.additional_sale_manager')->getAdditionalSaleForm($additionalSale);

                return $this->render('ClabWhiteBundle:AdditionalSale:additionalSaleForm.html.twig', array(
                    'product' => $meal,
                    'formAddSale' => $form->createView(),
                    'restaurant' => $meal->getRestaurant(),
                ));
            }
        } else {
            return $this->redirectToRoute('clab_white_order_home', array('slug' => $meal->getRestaurant()->getSlug()));
        }
    }

    /**
     * @ParamConverter("product", class="ClabRestaurantBundle:Product", options={"repository_method" = "findOneAvailable"})
     */
    public function getAdditionalSaleOptionFormAction(Request $request, Product $product)
    {
        $em = $this->getDoctrine()->getManager();

        $form = $this->get('app_restaurant.product_option_manager')->getOptionFormForProduct($product);

        $params = array(
            'product' => $product,
            'form' => $form->createView(),
        );

        if (in_array($product->getId(), array(10436, 10464))) {
            $params['subway'] = true;
        }

        return $this->render('ClabWhiteBundle:AdditionalSale:productOptionForm.html.twig', $params);
    }
}
