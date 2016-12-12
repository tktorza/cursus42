<?php

namespace Clab\ManagerBundle\Controller;

use Clab\RestaurantBundle\Entity\Meal;
use Clab\RestaurantBundle\Entity\OptionChoice;
use Clab\RestaurantBundle\Entity\Product;
use Clab\RestaurantBundle\Entity\Restaurant;
use Doctrine\Common\Collections\ArrayCollection;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class CatalogController extends Controller
{
    /**
     * @ParamConverter("restaurant", class="ClabRestaurantBundle:Restaurant")
     */
    public function getCatalogAction(Request $request, Restaurant $restaurant)
    {
        if (!is_null($restaurant)) {
            $em = $this->getDoctrine()->getEntityManager();
            $products = $em->getRepository('ClabRestaurantBundle:Product')->getForRestaurant($restaurant);
            $meals = $em->getRepository('ClabRestaurantBundle:Meal')->getForRestaurant($restaurant);
            $options = new ArrayCollection();

            $products = new ArrayCollection($products);
            $iteratorProducts = $products->getIterator();
            $iteratorProducts->uasort(function ($a, $b) {
                return ($a->getName() < $b->getName()) ? -1 : 1;
            });
            $products = new ArrayCollection(iterator_to_array($iteratorProducts));

            $meals = new ArrayCollection($meals);
            $iteratorMeals = $meals->getIterator();
            $iteratorMeals->uasort(function ($a, $b) {
                return ($a->getName() < $b->getName()) ? -1 : 1;
            });
            $meals = new ArrayCollection(iterator_to_array($iteratorMeals));

            foreach ($products as $product) {
                foreach ($product->getOptions() as $opt) {
                    if (!$options->contains($opt)) {
                        $options->add($opt);
                    }
                }
            }

            $choices = new ArrayCollection();

            foreach ($options as $opt) {
                foreach ($opt->getChoices() as $choice) {
                    $choices->add($choice);
                }
            }

            $iteratorOptions = $choices->getIterator();
            $iteratorOptions->uasort(function ($a, $b) {
                return ($a->getValue() < $b->getValue()) ? -1 : 1;
            });
            $options = new ArrayCollection(iterator_to_array($iteratorOptions));


            return $this->render('ClabManagerBundle:Dashboard2:catalogQuickEdit.html.twig', array(
                    'restaurant' => $restaurant,
                    'products' => $products,
                    'meals' => $meals,
                    'options' => $options,
                ));
        } else {
            return new NotFoundHttpException();
        }
    }

    public function updateProductOnlineAction(Request $request, $slug, $isOnline)
    {
        if ($request->isXmlHttpRequest()) {
            $product = $this->getDoctrine()->getEntityManager()->getRepository('ClabRestaurantBundle:Product')->findOneBy(array('slug' => $slug));
            if (!is_null($product)) {
                $product->setIsOnline(($isOnline == 'true' ? true : false));
                $this->getDoctrine()->getEntityManager()->flush();

                return new JsonResponse('Success');
            } else {
                return new JsonResponse('Error');
            }
        }

        return new JsonResponse('Error');
    }

    public function updateMealOnlineAction(Request $request, $slug, $isOnline)
    {
        if ($request->isXmlHttpRequest()) {
            $meal = $this->getDoctrine()->getEntityManager()->getRepository('ClabRestaurantBundle:Meal')->findOneBy(array('slug' => $slug));
            if (!is_null($meal)) {
                $meal->setIsOnline(($isOnline == 'true' ? true : false));
                $this->getDoctrine()->getEntityManager()->flush();

                return new JsonResponse('Success');
            } else {
                return new JsonResponse('Error');
            }
        }

        return new JsonResponse('Error');
    }

    public function updateOptionChoiceOnlineAction(Request $request, $id, $isOnline)
    {
        if ($request->isXmlHttpRequest()) {
            $optionChoice = $this->getDoctrine()->getEntityManager()->getRepository('ClabRestaurantBundle:OptionChoice')->findOneBy(array('id' => $id));
            if (!is_null($optionChoice)) {
                $optionChoice->setIsOnline(($isOnline == 'true' ? true : false));
                $this->getDoctrine()->getEntityManager()->flush();

                return new JsonResponse('Success');
            } else {
                return new JsonResponse('Error');
            }
        }

        return new JsonResponse('Error');
    }
}
