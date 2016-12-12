<?php

namespace Clab\UserBundle\Service;

use Clab\RestaurantBundle\Entity\Product;
use Clab\RestaurantBundle\Entity\Restaurant;
use Clab\ReviewBundle\Entity\Review;
use Doctrine\ORM\EntityManager;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\Routing\Router;
use Clab\UserBundle\Entity\User;

class UserManager
{
    protected $container;
    protected $em;
    protected $router;

    public function __construct(ContainerInterface $container, EntityManager $em, Router $router)
    {
        $this->container = $container;
        $this->em = $em;
        $this->router = $router;
    }

    public function isUserGranted($user, $role)
    {
        $userRoles = $user->getRoles();

        if (in_array($role, $userRoles)) {
            return true;
        }
        foreach ($userRoles as $userRole) {
            if ($this->roleOwnsRole($userRole, $role)) {
                return true;
            }
        }

        return false;
    }    

    public function addFavorite(User $user,Restaurant $restaurant)
    {
        if(is_null($user->getFavorites()) || !isset($user->getFavorites()[$restaurant->getId()])) {
            $user->addFavorite($restaurant);
            $this->em->flush();
        }

        return true;
    }

    public function removeFavorite(User $user, Restaurant $restaurant)
    {
        if(isset($user->getFavorites()[$restaurant->getId()])) {
            $user->removeFavorite($restaurant);
            $this->em->flush();
        }

        return true;
    }

    public function removeCompany(User $user)
    {
        $user->setCompany(null);
        $this->em->flush();

        return true;
    }

    public function isInFavorite(User $user, Restaurant $restaurant)
    {

        $favorites = $user->getFavorites();
        $result = isset($favorites[$restaurant->getId()]);

        return $result;
    }
    public function favoriteProductForRestaurant(User $user, Restaurant $restaurant)
    {
        $results = array();
        $user = $this->em->getRepository('ClabUserBundle:User')->find($user);
        $favorites = $user->getFavoriteProducts();
        if (!is_null($favorites)) {
            foreach ($favorites as $favorite) {
                $entity = $this->em->getRepository('ClabRestaurantBundle:Product')->find($favorite);
                if ($entity->getRestaurant() == $restaurant) {
                    $results[] = $favorite->getId();
                }
            }
        }

        return $results;
    }

    public function addFavoriteProduct(User $user, Product $product)
    {
        $user->addFavoriteProduct($product);
        $this->em->flush();

        return true;
    }

    public function addDiscount(User $user, $discount)
    {
        $user->addDiscount($discount);
        $this->em->flush();

        return true;
    }

    public function resetDiscount()
    {
        $users = $this->em->getRepository('ClabUserBundle:User')->findUsersHasDiscount();
        foreach ($users as $user) {
            foreach ($user->getDiscounts() as $discount) {
                $discount['status'] = 0;
            }
        }

        return true;
    }

    public function removeFavoriteProduct(User $user, Product $product)
    {
        if (($key = array_search($product, $user->getFavorites())) !== false) {
            unset($user->getFavoriteProducts()[$key]);
        }
        $this->em->flush();

        return true;
    }

    private function roleOwnsRole($masterRole, $slaveRole, $checkvalidityroles = true, $hierarchy = null)
    {
        if (!$hierarchy) {
            $hierarchy = $this->container->getParameter('security.role_hierarchy.roles');
        }
        if ($masterRole === $slaveRole) {
            return false;
        }
        if ($checkvalidityroles && (!array_key_exists($masterRole, $hierarchy) || !array_key_exists($slaveRole, $hierarchy))) {
            return false;
        }

        $masterRoles = $hierarchy[$masterRole];
        if (in_array($slaveRole, $masterRoles)) {
            return true;
        } else {
            foreach ($masterRoles as $masterRoleRec) {
                if ($this->roleOwnsRole($masterRoleRec, $slaveRole, false, $hierarchy)) {
                    return true;
                }
            }

            return false;
        }
    }

    public function getEmployeesForProxy($proxy)
    {
        $users = $proxy->getManagers();
        $roles = array_keys(User::getManagerRoles());

        foreach ($users as $key => $user) {
            if (count(array_intersect($user->getRoles(), $roles)) == 0) {
                unset($users[$key]);
            }
        }

        return $users;
    }

    public function getEmployeeForProxy($proxy, $id)
    {
        $users = $this->getEmployeesForProxy($proxy);

        foreach ($users as $user) {
            if ($user->getId() == $id) {
                return $user;
            }
        }

        return;
    }

    public function getSingleForProxy($proxy, $id)
    {
        $parameters = array(
            'is_deleted' => false,
            'id' => $id,
        );

        if ($proxy instanceof \Clab\RestaurantBundle\Entity\Restaurant) {
            $parameters['restaurant'] = $proxy;
        } else {
            $parameters['client'] = $proxy;
        }

        return $this->repository->findOneBy($parameters);
    }

    public function generateUserToken()
    {
        $users = $this->em->getRepository('ClabUserBundle:User')->findBy(array(
            'loginToken' => null,
        ));
        $count = 0;
        foreach ($users as $user) {
            $user->setLoginToken(sha1(uniqid(mt_rand(), true)).$user->getId());
            $this->em->flush();
            if ($count % 100 == 0) {
                $this->em->flush();
            }

            ++$count;
        }
        $this->em->flush();

        return true;
    }

    public function migrateFavorite()
    {
        $userArray = array();
        $users = $this->em->getRepository('ClabUserBundle:User')->findAll();
        foreach ($users as $user) {
            $userArray[$user->getId()] = $user;
        }

        $bookmarks = array(
            array(1, 'our-kebab'),
            array(1, 'le-macadam'),
            array(1, 'le-camion-qui-fume-i'),
            array(1, 'gastr-o-diet'),
            array(1, 'coralie-s-kitchen'),
            array(1, 'la-camionnette'),
            array(1, 'le-bagel-qui-roule'),
            array(1, 'masterchef-foodtruck'),
            array(1, 'le-vagabond'),
            array(1, 'pitakia-1'),
            array(1, 'new-soul-food'),
            array(1, 'route-69'),
            array(10, 'our-kebab'),
            array(10, 'oh-saveurs'),
            array(10, 'kok-ping'),
            array(14, 'waffle-factory'),
            array(15, 'our-kebab'),
            array(15, 'cantine-california'),
            array(15, 'le-camion-qui-fume-i'),
            array(15, 'eat-the-road'),
            array(15, 'le-burger-truck'),
            array(15, 'maroc-street-food'),
            array(15, 'new-soul-food'),
            array(15, 'cafeine-cafe-out'),
            array(16, 'le-refectoire'),
            array(16, 'so-good-bagel'),
            array(16, 'el-tacot'),
            array(16, 'eat-my-truck'),
            array(16, 'cantine-california'),
            array(16, 'le-macadam'),
            array(16, 'le-camion-gourmand'),
            array(16, 'le-camion-qui-fume-i'),
            array(16, 'delistreet'),
            array(16, 'le-camion-qui-fume-ii'),
            array(16, 'eat-the-road'),
            array(16, 'le-vieux-taco'),
            array(16, 'cocotte-cantine'),
            array(16, 'black-spoon'),
            array(16, 'yellow-bus'),
            array(16, 'gastr-o-diet'),
            array(16, 'coralie-s-kitchen'),
            array(16, 'after-eleven'),
            array(16, 'bouche-b'),
            array(16, 'la-fourchette-qui-roule'),
            array(16, 'sushiju'),
            array(16, 'kiosque-dim-sum'),
            array(16, 'el-camion-cantina-mexicana'),
            array(16, 'le-kitchn'),
            array(16, 'hansel-bretzel'),
            array(16, 'tartobus'),
            array(16, 'r-b-foodtruck'),
            array(16, 'on-mobile-burger'),
            array(16, 'le-truck-a-part'),
            array(16, 'gourmet-trotteur'),
            array(16, 'le-beau-caillou'),
            array(16, 'le-bagel-qui-roule'),
            array(16, 'la-brigade'),
            array(16, 'la-cantina-de-gloria'),
            array(17, 'le-refectoire'),
            array(17, 'mahotte'),
            array(17, 'flammatruck'),
            array(17, 'our-kebab-foodtruck'),
            array(17, 'le-truck-a-manger'),
            array(36, 'think-p-m'),
            array(83, 'so-good-bagel'),
            array(112, 'le-refectoire'),
            array(125, 'so-good-bagel'),
            array(143, 'so-good-bagel'),
            array(145, 'so-good-bagel'),
            array(150, 'so-good-bagel'),
            array(167, 'so-good-bagel'),
            array(249, 'beytouti-truck'),
            array(283, 'le-refectoire'),
            array(283, 'cantine-california'),
            array(283, 'le-macadam'),
            array(283, 'le-camion-qui-fume-i'),
            array(283, 'gastr-o-diet'),
            array(283, 'coralie-s-kitchen'),
            array(283, 'after-eleven'),
            array(283, 'sushiju'),
            array(286, 'cantine-california'),
            array(286, 'delistreet'),
            array(286, 'la-brigade'),
            array(305, 'mahotte'),
            array(305, 'cantine-california'),
            array(305, 'eat-the-road'),
            array(305, 'cocotte-cantine'),
            array(305, 'our-kebab-foodtruck'),
            array(305, 'kiosque-dim-sum'),
            array(305, 'korrigans'),
            array(541, 'so-gourmet'),
            array(570, 'star-truck'),
            array(585, 'l-emporte-bonheur'),
            array(641, 'cantine-california'),
            array(684, 'le-camion-qui-fume-i'),
            array(696, 'our-kebab-foodtruck'),
            array(696, 'el-camion-cantina-mexicana'),
            array(699, 'r-b-foodtruck'),
            array(747, 'le-bagel-qui-roule'),
            array(754, 'bouche-b'),
            array(757, 'yellow-bus'),
            array(812, 'el-tacot'),
            array(812, 'le-camion-qui-fume-ii'),
            array(812, 'kiosque-dim-sum'),
            array(828, 'le-camion-qui-fume-i'),
            array(837, 'eat-my-truck'),
            array(849, 'le-refectoire'),
            array(849, 'cantine-california'),
            array(849, 'le-camion-qui-fume-i'),
            array(855, 'le-refectoire'),
            array(855, 'cantine-california'),
            array(855, 'le-camion-qui-fume-i'),
            array(855, 'le-camion-qui-fume-ii'),
            array(855, 'la-brigade'),
            array(855, 'le-camion-qui-fume-iii'),
            array(884, 'le-camion-qui-fume-i'),
            array(884, 'le-camion-qui-fume-ii'),
            array(889, 'cantine-california'),
            array(889, 'le-camion-qui-fume-i'),
            array(907, 'le-camion-qui-fume-i'),
            array(907, 'tooq-tooq'),
            array(954, 'le-refectoire'),
            array(954, 'cantine-california'),
            array(954, 'le-camion-qui-fume-i'),
            array(958, 'le-refectoire'),
            array(958, 'cocotte-cantine'),
            array(961, 'le-camion-qui-fume-ii'),
            array(961, 'tooq-tooq'),
            array(1005, 'le-refectoire'),
            array(1005, 'cantine-california'),
            array(1005, 'le-camion-qui-fume-ii'),
            array(1005, 'coralie-s-kitchen'),
            array(1006, 'delistreet'),
            array(1008, 'le-refectoire'),
            array(1008, 'cantine-california'),
            array(1008, 'le-camion-qui-fume-i'),
            array(1008, 'le-camion-qui-fume-ii'),
            array(1008, 'la-brigade'),
            array(1012, 'le-camion-qui-fume-i'),
            array(1012, 'la-brigade'),
            array(1017, 'cantine-california'),
            array(1017, 'le-camion-qui-fume-i'),
            array(1018, 'le-refectoire'),
            array(1018, 'le-camion-qui-fume-i'),
            array(1018, 'le-camion-qui-fume-ii'),
            array(1018, 'le-canard-huppe'),
            array(1025, 'le-camion-qui-fume-i'),
            array(1031, 'le-refectoire'),
            array(1033, 'le-refectoire'),
            array(1038, 'le-refectoire'),
            array(1038, 'cantine-california'),
            array(1042, 'le-camion-qui-fume-i'),
            array(1054, 'le-camion-qui-fume-i'),
            array(1054, 'after-eleven'),
            array(1101, 'le-camion-qui-fume-i'),
            array(1149, 'le-refectoire'),
            array(1149, 'mahotte'),
            array(1149, 'cantine-california'),
            array(1149, 'le-camion-qui-fume-i'),
            array(1149, 'le-truck-a-part'),
            array(1149, 'charlie-streetfood'),
            array(1176, 'le-canard-huppe'),
            array(1191, 'le-refectoire'),
            array(1191, 'cantine-california'),
            array(1191, 'le-camion-qui-fume-i'),
            array(1191, 'le-camion-qui-fume-iii'),
            array(1197, 'le-macadam'),
            array(1203, 'le-truck-a-manger'),
            array(1211, 'la-fourchette-qui-roule'),
            array(1220, 'la-camionnette'),
            array(1228, 'cantine-california'),
            array(1228, 'our-kebab-foodtruck'),
            array(1228, 'la-brigade'),
            array(1249, 'cantine-california'),
            array(1255, 'le-refectoire'),
            array(1255, 'cantine-california'),
            array(1255, 'le-camion-qui-fume-i'),
            array(1255, 'le-camion-qui-fume-ii'),
            array(1255, 'eat-the-road'),
            array(1255, 'cocotte-cantine'),
            array(1255, 'le-camion-qui-fume-iii'),
            array(1304, 'le-refectoire'),
            array(1304, 'flammatruck'),
            array(1304, 'after-eleven'),
            array(1305, 'le-refectoire'),
            array(1305, 'le-camion-qui-fume-i'),
            array(1305, 'le-camion-qui-fume-ii'),
            array(1305, 'le-camion-qui-fume-iii'),
            array(1309, 'la-camionnette'),
            array(1309, 'la-cantine-qui-trottine'),
            array(1316, 'on-mobile-burger'),
            array(1373, 'un-truck-de-ouf'),
            array(1374, 'coralie-s-kitchen'),
            array(1396, 'l-emporte-bonheur'),
            array(1405, 'le-camion-qui-fume-iii'),
            array(1419, 'le-truck-a-manger'),
            array(1447, 'le-refectoire'),
            array(1447, 'la-brigade'),
            array(1447, 'le-camion-qui-fume-iii'),
            array(1449, 'le-camion-qui-fume-i'),
            array(1449, 'eat-the-road'),
            array(1464, 'wok-n-go'),
            array(1471, 'le-camion-qui-fume-i'),
            array(1475, 'le-refectoire'),
            array(1475, 'cantine-california'),
            array(1475, 'bugelski'),
            array(1475, 'le-camion-qui-fume-i'),
            array(1475, 'le-camion-qui-fume-ii'),
            array(1475, 'so-gourmet'),
            array(1475, 'le-camion-qui-fume-iii'),
            array(1495, 'le-refectoire'),
            array(1495, 'mahotte'),
            array(1495, 'cantine-california'),
            array(1495, 'bugelski'),
            array(1495, 'le-camion-qui-fume-i'),
            array(1495, 'le-camion-qui-fume-ii'),
            array(1495, 'eat-the-road'),
            array(1495, 'la-brigade'),
            array(1514, 'le-camion-qui-fume-i'),
            array(1545, 'le-camion-qui-fume-ii'),
            array(1585, 'le-truck-a-manger'),
            array(1588, 'le-camion-qui-fume-i'),
            array(1603, 'ma-cuisine-bleue'),
            array(1641, 'eat-the-road'),
            array(1641, 'yellow-bus'),
            array(1641, 'zesto'),
            array(1641, 'la-fourchette-qui-roule'),
            array(1641, 'le-canard-huppe'),
            array(1641, 'kiosque-dim-sum'),
            array(1641, 'r-b-foodtruck'),
            array(1641, 'la-brigade'),
            array(1653, 'le-camion-qui-fume-i'),
            array(1653, 'eat-the-road'),
            array(1653, 'on-mobile-burger'),
            array(1653, 'le-truck-a-manger'),
            array(1653, 'korrigans'),
            array(1653, 'nakedfrog'),
            array(1653, 'masterchef-foodtruck'),
            array(1665, 'le-camion-gourmand'),
            array(1665, 'eat-the-road'),
            array(1666, 'le-camion-qui-fume-ii'),
            array(1666, 'la-brigade'),
            array(1694, 'cantine-california'),
            array(1694, 'le-camion-qui-fume-ii'),
            array(1694, 'eat-the-road'),
            array(1727, 'le-refectoire'),
            array(1727, 'cantine-california'),
            array(1727, 'eat-the-road'),
            array(1727, 'black-spoon'),
            array(1727, 'le-canard-huppe'),
            array(1727, 'la-cantina-de-gloria'),
            array(1727, 'kgb-le-k-mion-gourmet-burger'),
            array(1731, 'le-camion-qui-fume-i'),
            array(1736, 'le-refectoire'),
            array(1736, 'cantine-california'),
            array(1736, 'le-camion-qui-fume-i'),
            array(1736, 'goodys'),
            array(1736, 'le-camion-qui-fume-ii'),
            array(1736, 'la-brigade'),
            array(1736, 'le-camion-qui-fume-iii'),
            array(1783, 'el-tacot'),
            array(1783, 'cantine-california'),
            array(1783, 'la-brigade'),
            array(1815, 'masterchef-foodtruck'),
            array(1818, 'delistreet'),
            array(1845, 'gourmet-trotteur'),
            array(1863, 'le-beau-caillou'),
            array(1863, 'breizh-truck'),
            array(1863, 'korrigans'),
            array(1863, 'masterchef-foodtruck'),
            array(1879, 'cantine-california'),
            array(1898, 'la-cantine-qui-trottine'),
            array(1915, 'tooq-tooq'),
            array(1917, 'le-camyon'),
            array(1923, 'bouche-b'),
            array(1923, 'hansel-bretzel'),
            array(1923, 'les-mecs-au-camion'),
            array(1924, 'cantine-california'),
            array(1924, 'le-camion-qui-fume-i'),
            array(1924, 'le-camion-qui-fume-ii'),
            array(1924, 'le-camion-qui-fume-iii'),
            array(1972, 'miam-zelle-agnes-3'),
            array(1981, 'le-refectoire'),
            array(1981, 'cantine-california'),
            array(1981, 'bugelski'),
            array(1981, 'le-camion-gourmand'),
            array(1981, 'goodys'),
            array(1981, 'eat-the-road'),
            array(1981, 'le-bagel-qui-roule'),
            array(1981, 'tooq-tooq'),
            array(1981, 'la-brigade'),
            array(1981, 'breizh-truck'),
            array(1996, 'american-coffee-4'),
            array(2024, 'breizh-truck'),
            array(2037, 'la-brigade'),
            array(2099, 'cantine-california'),
            array(2099, 'le-camion-qui-fume-i'),
            array(2105, 'le-beau-caillou'),
            array(2109, 'le-camion-qui-fume-ii'),
            array(2109, 'la-brigade'),
            array(2171, 'le-refectoire'),
            array(2171, 'cantine-california'),
            array(2171, 'le-camion-qui-fume-i'),
            array(2171, 'le-camion-qui-fume-ii'),
            array(2171, 'le-camion-qui-fume-iii'),
            array(2171, 'masterchef-foodtruck'),
            array(2171, 'the-sunken-chip'),
            array(2204, 'greengourmet-le-camion-gourmand'),
            array(2258, 'wokers'),
            array(2271, 'black-rhino'),
            array(2341, 'la-marmite-qui-roule'),
            array(2361, 'le-camion-qui-fume-i'),
            array(2361, 'le-camion-qui-fume-ii'),
            array(2361, 'la-brigade'),
            array(2361, 'the-sunken-chip'),
            array(2389, 'greengourmet-le-camion-gourmand'),
            array(2391, 'le-burger-de-choc'),
            array(2399, 'le-refectoire'),
            array(2399, 'kiosque-dim-sum'),
            array(2399, 'el-camion-cantina-mexicana'),
            array(2399, 'tooq-tooq'),
            array(2399, 'la-brigade'),
            array(2399, 'the-sunken-chip'),
            array(2445, 'black-rhino'),
            array(2459, 'herve-food-truck-gourmand'),
            array(2475, 'mister-thot-burger'),
            array(2543, 'cantine-california'),
            array(2560, 'mister-thot-burger'),
            array(2595, 'black-rhino'),
            array(2600, 'toulet-tout-beurre-tout-creme'),
            array(2618, 'chez-luce'),
            array(2635, 'food-truck-indien'),
            array(2666, 'le-camion-qui-fume-i'),
            array(2666, 'le-camion-qui-fume-ii'),
            array(2666, 'tapaseb-foodtruck'),
            array(2670, 'le-refectoire'),
            array(2670, 'la-brigade'),
            array(2676, 'breizh-truck'),
            array(2677, 'le-camion-qui-fume-i'),
            array(2677, 'crepes-troopers'),
            array(2686, 'cantine-california'),
            array(2686, 'le-camion-qui-fume-i'),
            array(2686, 'the-sunken-chip'),
            array(2686, 'streat-parade-grill'),
            array(2694, 'tooq-tooq'),
            array(2694, 'le-globe-trotteur-cuisine'),
            array(2709, 'le-camion-qui-fume-i'),
            array(2709, 'le-camion-qui-fume-ii'),
            array(2731, 'bouche-b'),
            array(2731, 'hansel-bretzel'),
            array(2731, 'miam-thai'),
            array(2736, 'mister-thot-burger'),
            array(2738, 'un-camion-dans-la-ville'),
            array(2756, 'pitakia-1'),
            array(2763, 'le-truck-a-manger'),
            array(2771, 'le-truck-a-manger'),
            array(2771, 'le-marmit-truck'),
            array(2771, 'tototruck'),
            array(2774, 'krys-burgers'),
            array(2780, 'black-spoon'),
            array(2792, 'le-camion-qui-fume-ii'),
            array(2792, 'la-brigade'),
            array(2795, 'cantine-california'),
            array(2795, 'le-camion-qui-fume-ii'),
            array(2796, 'colin-s-burger'),
            array(2827, 'le-truck-a-manger'),
            array(2845, 'bugelski'),
            array(2863, 'globe-food-1'),
            array(2890, 'buzz-food-truck'),
            array(2901, 'flammatruck'),
            array(2902, 'le-camion-gourmand'),
            array(2902, 'le-camion-qui-fume-i'),
            array(2902, 'le-camion-qui-fume-ii'),
            array(2902, 'la-brigade'),
            array(2902, 'a-la-tete-du-client'),
            array(2902, 'l-hipster'),
            array(2902, 'a-casa'),
            array(2905, 'la-brigade'),
            array(2907, 'le-refectoire'),
            array(2907, 'delistreet'),
            array(2907, 'on-mobile-burger'),
            array(2907, 'le-bagel-qui-roule'),
            array(2907, 'charlie-streetfood'),
            array(2907, 'breizh-truck'),
            array(2907, 'crepes-troopers'),
            array(2907, 'le-camion-du-vexin'),
            array(2925, 'truck-2-food'),
            array(2925, 'sacrebleu'),
            array(2942, 'la-marmite-qui-roule'),
            array(2942, 'la-gam-ll-au-naturel'),
            array(2943, 'hansel-bretzel'),
            array(2948, 'r-b-foodtruck'),
            array(2948, 'cookooling'),
            array(2981, 'r-b-foodtruck'),
            array(2981, 'le-vagabond'),
            array(2981, 'cookooling'),
            array(2990, 'franch-country-truck'),
            array(2992, 'le-truck-a-manger'),
            array(2992, 'l-emporte-bonheur'),
            array(3013, 'la-brigade'),
            array(3025, 'le-camion-qui-fume-i'),
            array(3025, 'le-camion-qui-fume-ii'),
            array(3025, 'le-canard-huppe'),
            array(3025, 'la-brigade'),
            array(3073, 'la-brigade'),
            array(3082, 'r-b-foodtruck'),
            array(3082, 'joy'),
            array(3082, 'le-vagabond'),
            array(3082, 'sacrebleu'),
            array(3127, 'le-camion-qui-fume-i'),
            array(3157, 'son-of-a-bun-antibes'),
            array(3245, 'les-food-du-volant-1'),
            array(3267, 'pitakia-1'),
            array(3281, 'colin-s-burger'),
            array(3301, 'tototruck'),
            array(3317, 'occitania-food'),
            array(3326, 'krep-events'),
            array(3330, 'afro-delices'),
            array(3339, 'l-hipster'),
            array(3339, 'cheesers'),
            array(3364, 'lobster-co'),
            array(3368, 'le-macadam'),
            array(3368, 'coralie-s-kitchen'),
            array(3379, 'pitakia-1'),
            array(3384, 'l-h-et-vous'),
            array(3384, 'la-chill-zone-food-truck'),
            array(3384, 'lefoodtruck'),
            array(3421, 'coralie-s-kitchen'),
            array(3437, 'gastr-o-diet'),
            array(3437, 'coralie-s-kitchen'),
            array(3437, 'franch-country-truck'),
            array(3439, 'le-macadam'),
            array(3439, 'gastr-o-diet'),
            array(3439, 'coralie-s-kitchen'),
            array(3439, 'occitania-food'),
            array(3440, 'franch-country-truck'),
            array(3442, 'franch-country-truck'),
            array(3445, 'franch-country-truck'),
            array(3447, 'tooq-tooq'),
            array(3450, 'bouche-b'),
            array(3461, 'le-beau-caillou'),
            array(3469, 'le-macadam'),
            array(3469, 'gastr-o-diet'),
            array(3469, 'franch-country-truck'),
            array(3477, 'le-macadam'),
            array(3477, 'gastr-o-diet'),
            array(3477, 'coralie-s-kitchen'),
            array(3477, 'lobster-co'),
            array(3486, 'le-macadam'),
            array(3486, 'gastr-o-diet'),
            array(3486, 'coralie-s-kitchen'),
            array(3486, 'franch-country-truck'),
            array(3507, 'le-canard-huppe'),
            array(3520, 'le-canard-huppe'),
            array(3521, 'le-macadam'),
            array(3521, 'gastr-o-diet'),
            array(3521, 'coralie-s-kitchen'),
            array(3521, 'le-truck-normand'),
            array(3534, 'le-macadam'),
            array(3534, 'gastr-o-diet'),
            array(3534, 'coralie-s-kitchen'),
            array(3534, 'the-bagel-box'),
            array(3534, 'la-crepe-qui-roule'),
            array(3534, 'les-crepes-de-mamick'),
            array(3539, 'masterchef-foodtruck'),
            array(3561, 'breizh-truck'),
            array(3577, 'kiosque-dim-sum'),
            array(3577, 'breizh-truck'),
            array(3586, 'buzz-food-truck'),
            array(3654, 'la-petite-creperie-ambulante'),
            array(3671, 'coralie-s-kitchen'),
            array(3676, 'frichti-restomobile'),
            array(3687, 'black-spoon'),
            array(3687, 'cafeine-cafe-out'),
            array(3691, 'les-food-du-volant-1'),
            array(3701, 'franch-country-truck'),
            array(3724, 'lobster-co'),
            array(3732, 'eat-my-truck'),
            array(3733, 'le-camion-qui-fume-iii'),
            array(3742, 'buzz-food-truck'),
            array(3775, 'lobster-co'),
            array(3778, 'gastr-o-diet'),
            array(3778, 'so-gourmet'),
            array(3778, 'coralie-s-kitchen'),
            array(3780, 'le-camion-qui-fume-iii'),
            array(3796, 'franch-country-truck'),
            array(3804, 'buzz-food-truck'),
            array(3810, 'le-macadam'),
            array(3822, 'maa'),
            array(3829, 'le-macadam'),
            array(3829, 'coralie-s-kitchen'),
            array(3830, 'krep-events'),
            array(3830, 'fresh-minute'),
            array(3830, 'new-soul-food'),
            array(3830, 'urben-grillades-gourmandes'),
            array(3832, 'le-macadam'),
            array(3832, 'gastr-o-diet'),
            array(3832, 'coralie-s-kitchen'),
            array(3837, 'la-camionnette'),
            array(3846, 'buzz-food-truck'),
            array(3850, 'our-kebab-foodtruck'),
            array(3860, 'franch-country-truck'),
            array(3864, 'la-brigade'),
            array(3884, 'le-macadam'),
            array(3884, 'gastr-o-diet'),
            array(3884, 'r-b-foodtruck'),
            array(3884, 'le-camyon'),
            array(3884, 'les-food-du-volant-1'),
            array(3884, 'bcook-food-truck'),
            array(3884, 'herve-food-truck-gourmand'),
            array(3884, 'le-p-tit-olive'),
            array(3884, 'la-chill-zone-food-truck'),
            array(3908, 'cantine-california'),
            array(3908, 'gastr-o-diet'),
            array(3908, 'tooq-tooq'),
            array(3908, 'la-brigade'),
            array(3908, 'fresh-minute'),
            array(3908, 'u-m-m-m'),
            array(3908, 'caravanserail'),
            array(3908, 'ummy-le-yaourt-glace-bio'),
            array(3921, 'le-refectoire'),
            array(3921, 'le-macadam'),
            array(3921, 'le-camion-qui-fume-i'),
            array(3921, 'le-camion-qui-fume-ii'),
            array(3921, 'eat-the-road'),
            array(3921, 'our-kebab-foodtruck'),
            array(3921, 'gastr-o-diet'),
            array(3921, 'coralie-s-kitchen'),
            array(3921, 'la-brigade'),
            array(3921, 'le-comptoir-indien'),
            array(3921, 'louise-du-sud'),
            array(3933, 'gastr-o-diet'),
            array(3944, 'black-rhino'),
            array(3945, 'le-macadam'),
            array(3945, 'gastr-o-diet'),
            array(3945, 'coralie-s-kitchen'),
            array(3956, 'black-rhino'),
            array(3956, 'maki-co'),
            array(3956, 'le-burger-de-choc'),
            array(3992, 'mon-camion-courtepaille-bretigny-sur-orge'),
            array(3997, 'bugelski'),
            array(3997, 'kiosque-dim-sum'),
            array(3997, 'tooq-tooq'),
            array(3997, 'streat-parade-grill'),
            array(3997, 'lobster-co'),
            array(4006, 'le-macadam'),
            array(4006, 'gastr-o-diet'),
            array(4006, 'coralie-s-kitchen'),
            array(4006, 'le-truck-a-manger'),
            array(4008, 'le-burger-truck'),
            array(4016, 'frichti-restomobile'),
            array(4022, 'le-macadam'),
            array(4022, 'gastr-o-diet'),
            array(4022, 'coralie-s-kitchen'),
            array(4036, 'gastr-o-diet'),
            array(4036, 'coralie-s-kitchen'),
            array(4036, 'el-camion-cantina-mexicana'),
            array(4068, 'tooq-tooq'),
            array(4074, 'le-globe-trotteur-cuisine'),
            array(4078, 'coralie-s-kitchen'),
            array(4080, 'thai-express'),
            array(4081, 'tooq-tooq'),
            array(4082, 'la-camionnette'),
            array(4090, 'l-hambourg'),
            array(4096, 'pitakia-1'),
            array(4105, 'le-camion-qui-fume-ii'),
            array(4105, 'new-soul-food'),
            array(4105, 'u-m-m-m'),
            array(4108, 'eat-the-road'),
            array(4122, 'on-mobile-burger'),
            array(4124, 'breizh-truck'),
            array(4154, 'la-chill-zone-food-truck'),
            array(4168, 'le-macadam'),
            array(4168, 'gastr-o-diet'),
            array(4168, 'coralie-s-kitchen'),
            array(4177, 'l-hipster'),
            array(4183, 'l-hambourg'),
            array(4190, 'le-vieux-taco'),
            array(4190, 'coralie-s-kitchen'),
            array(4196, 'gastr-o-diet'),
            array(4196, 'coralie-s-kitchen'),
            array(4249, 'le-macadam'),
            array(4249, 'gastr-o-diet'),
            array(4249, 'coralie-s-kitchen'),
            array(4261, 'l-emporte-bonheur'),
            array(4272, 'sacrebleu'),
            array(4297, 'le-camion-gourmet'),
            array(4299, 'cantine-california'),
            array(4320, 'coralie-s-kitchen'),
            array(4320, 'crepes-troopers'),
            array(4343, 'new-soul-food'),
            array(4350, 'le-macadam'),
            array(4350, 'gastr-o-diet'),
            array(4350, 'coralie-s-kitchen'),
            array(4350, 'la-camionnette'),
            array(4387, 'the-mother-road'),
            array(4399, 'buzz-food-truck-fish-and-chips'),
            array(4406, 'tooq-tooq'),
            array(4427, 'le-vieux-taco'),
            array(4458, 'l-emporte-bonheur'),
            array(4459, 'le-macadam'),
            array(4459, 'gastr-o-diet'),
            array(4459, 'coralie-s-kitchen'),
            array(4464, 'b-gourmet'),
            array(4510, 'le-macadam'),
            array(4510, 'gastr-o-diet'),
            array(4510, 'coralie-s-kitchen'),
            array(4535, 'le-macadam'),
            array(4535, 'gastr-o-diet'),
            array(4535, 'coralie-s-kitchen'),
            array(4543, 'fresh-minute'),
            array(4554, 'axebon'),
            array(4556, 'le-macadam'),
            array(4556, 'gastr-o-diet'),
            array(4556, 'coralie-s-kitchen'),
            array(4571, 'coralie-s-kitchen'),
            array(4571, 'on-mange-thai'),
            array(4586, 'la-chill-zone-food-truck'),
            array(4624, 'l-hambourg'),
            array(4660, 'le-macadam'),
            array(4660, 'gastr-o-diet'),
            array(4660, 'coralie-s-kitchen'),
            array(4690, 'le-macadam'),
            array(4690, 'coralie-s-kitchen'),
            array(4690, 'occitania-food'),
            array(4708, 'le-macadam'),
            array(4728, 'el-camion-cantina-mexicana'),
            array(4809, 'bugelski'),
            array(4809, 'le-camion-qui-fume-ii'),
            array(4809, 'le-beau-caillou'),
            array(4809, 'la-brigade'),
            array(4809, 'nolita-street'),
            array(4866, 'le-macadam'),
            array(4866, 'gastr-o-diet'),
            array(4866, 'coralie-s-kitchen'),
            array(4877, 'le-macadam'),
            array(4893, 'buzz-food-truck-fish-and-chips'),
            array(4902, 'buzz-food-truck'),
            array(4956, 'la-cantine-qui-trottine'),
            array(5001, 'la-marmite-qui-roule'),
            array(5002, 'le-canard-huppe'),
            array(5022, 'buzz-food-truck-fish-and-chips'),
            array(5036, 'un-sourire-dans-l-assiette'),
            array(5041, 'bugelski'),
            array(5041, 'eat-the-road'),
        );

        $count = 0;
        foreach ($bookmarks as $bookmark) {
            $user = $userArray[$bookmark[0]];
            $user->addFavorite($bookmark[1]);
            ++$count;

            if ($count % 100 == 0) {
                $this->em->flush();
            }
        }
    }

    /**
     * {@inheritDoc}
     */
    public function findUserBy(array $criteria)
    {
        return $this->em->getRepository(User::class)->findOneBy($criteria);
    }

    public function getFavorites($user)
    {
        return $this->em->getRepository(User::class)->findFavorite($user);
    }

    public function getReviews($user)
    {
        $repository = $this->em->getRepository(Review::class);

        $query = $repository->createQueryBuilder('review')
            ->select('review')
            ->where('review.profile= :id')
            ->setParameter('id',$user->getId())
            ->getQuery();
        
        $results = $query->getResult();

        return $results;
    }

    public function getFavoritesAPI(User $user)
    {
        return $this->em->getRepository(Restaurant::class)->findFavorites(implode($user->getFavorites(), '|'));
    }
    
    
    public function findUserByPhone($phone)
    {
        $phone = preg_replace("/[\s.-]*/",'',$phone);
        $phoneSuffix = preg_replace('/^(0|\+33|33)*/','',$phone);
        $phones = array('+33'.$phoneSuffix,'33'.$phoneSuffix,'0'.$phoneSuffix);
 
        return $this->em->getRepository(User::class)->findUserByPhone($phones);
    }
}
