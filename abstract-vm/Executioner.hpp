
#include "Parseur.hpp"

class Executioner {
    Executioner(Parseur *parseur);
}

Executioner::Executioner(Parseur *parseur) : parseur(parseur){}

Executioner::vmStart(){
    for (parseur.begin())
}