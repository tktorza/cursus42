<?php

if (!file_exists("../private"))
	mkdir("../private");
else
{
	return ;
}
session_destroy();
//echo "Creation db data\n";
$item1 = array('id' => '1','name' => 'Europeen Adulte', 'description' => 'Esclave blanc type Europeen age adulte, voir cadre legal(35h/semaine, 5 semaines de congees/an, adherent au club des joyeux esclaves modernes', 'price' => 500000, 'img' => 'homme2.png');
$item2 = array('id' => '2','name' => 'Asiatique Adulte', 'description' => 'Esclave type Asiatique age adulte, vous fera decouvrir les saveurs du Soleil levant.', 'price' => 300000, 'img' => 'chinois.png');
$item3 = array('id' => '3','name' => 'Africain Adulte', 'description' => 'Esclave type Africain, travailleur et discret. Parfait pour un travail agricole.', 'price' => 100000, 'img' => 'africain.jpg');
$item4 = array('id' => '4','name' => 'Americain Adulte', 'description' => 'Esclave type Americain age adulte. Forte corpulence, ideal pour laisser pourrir sur le cannape devant un tlak show(ellen de degeneres, ...), (nourriture : 1,8kg boeuf/jour, sodas, NE LE NOURRISSEZ QUE AVEC DES OGMS).' , 'price' => '4', 'img' => 'americain.png');
$item5 = array('id' => '5','name' => 'Europeen Enfant', 'description' => 'Esclave enfant type Europeen', 'price' => 250000, 'img' => 'europeen.jpg');
$item6 = array('id' => '6','name' => 'Asiatique Enfant', 'description' => 'Esclave enfant type Asiatique', 'price' => 150000, 'img' => 'asiatchild.jpg');
$item7 = array('id' => '7','name' => 'Africain Enfant', 'description' => 'Esclave enfant type Africain', 'price' => 50000, 'img' => 'africainchild.jpg');
$item8 = array('id' => '8','name' => 'Americain Enfant', 'description' => 'Esclave enfant type Americain', 'price' => 1, 'img' => 'obese.jpg');
$data = array($item1, $item2, $item3, $item4, $item5, $item6, $item7, $item8);
file_put_contents("../private/data", serialize($data));
//echo "Creation db cat\n";
$cat1 = array('id' => '1', 'name' => 'Europeen');
$cat2 = array('id' => '2', 'name' => 'Asiatique');
$cat3 = array('id' => '3', 'name' => 'Americain');
$cat4 = array('id' => '4', 'name' => 'Africain');
$cat5 = array('id' => '5', 'name' => 'tous');
$dcat = array($cat1, $cat2, $cat3, $cat4, $cat5);
file_put_contents("../private/cat", serialize($dcat));
//echo "Creation lien cat/article\n";
$lien1 = array('id_item' => '1','id_cat' => '1');
$lien2 = array('id_item' => '1','id_cat' => '5');
$lien3 = array('id_item' => '2','id_cat' => '5');
$lien4 = array('id_item' => '2','id_cat' => '2');
$lien5 = array('id_item' => '3','id_cat' => '4');
$lien6 = array('id_item' => '3','id_cat' => '5');
$lien7 = array('id_item' => '4','id_cat' => '5');
$lien8 = array('id_item' => '4','id_cat' => '3');
$lien9 = array('id_item' => '5','id_cat' => '5');
$lien10 = array('id_item' => '5','id_cat' => '1');
$lien11 = array('id_item' => '6','id_cat' => '5');
$lien12 = array('id_item' => '6','id_cat' => '2');
$lien13 = array('id_item' => '7','id_cat' => '4');
$lien14 = array('id_item' => '7','id_cat' => '5');
$lien15 = array('id_item' => '8','id_cat' => '5');
$lien16 = array('id_item' => '8','id_cat' => '3');
$dlien = array($lien1, $lien2, $lien3, $lien4, $lien4, $lien5, $lien6, $lien7, $lien8, $lien9, $lien10, $lien11, $lien12, $lien13, $lien14, $lien15, $lien16,);
file_put_contents("../private/lien", serialize($dlien));
//echo "Creation db user\n";
$user1 = array('login' => 'user1', 'passwd' => hash('whirlpool', 'user1'), 'droit' => '0');
$user2 = array('login' => 'user2', 'passwd' => hash('whirlpool', 'user2'), 'droit' => '0');
$user3 = array('login' => 'user3', 'passwd' => hash('whirlpool', 'user3'), 'droit' => '0');
$user4 = array('login' => 'user4', 'passwd' => hash('whirlpool', 'user4'), 'droit' => '0');
$admin = array('login' => 'admin', 'passwd' => hash('whirlpool', 'admin'), 'droit' => '1');
$duser = array($user1, $user2, $user3, $user4, $admin);
file_put_contents("../private/passwd", serialize($duser));

?>
