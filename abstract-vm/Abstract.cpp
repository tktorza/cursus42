#include "Abstract.hpp"

void Abstract::push(std::string const & str, std::string const & name)
{
	int ok = 0;
	std::string type[5] = {};
	for (int i=name.length();i<str.c_str().length();i++){
		if (str.c_str()[i] != ' '){
			for (int x=0;x<5;x++;){
				if (strncmp(&str.c_str()[i], type[x].c_str(), type[x].length()) == 0){

					ok = 1;
			}

		}
		// if (ok == 0)
		// 	std::error << "Unknow type after push line" << this->line << std::endl;
		
	}
	std::cout << str << std::endl;
}

void Abstract::pop(std::string const & str)
{
	std::cout << str << std::endl;
}

void Abstract::dump(std::string const & str)
{
	std::cout << str << std::endl;
}


void Abstract::assert(std::string const & str)
{
	std::cout << str << std::endl;
}

void Abstract::add(std::string const & str)
{
	std::cout << str << std::endl;
}

void Abstract::sub(std::string const & str)
{
	std::cout << str << std::endl;
}

void Abstract::mul(std::string const & str)
{
	std::cout << str << std::endl;
}

void Abstract::div(std::string const & str)
{
	std::cout << str << std::endl;
}


void Abstract::mod(std::string const & str)
{
	std::cout << str << std::endl;
}

void Abstract::print(std::string const & str)
{
	std::cout << str << std::endl;
}

void Abstract::exit(std::string const & str)
{
	std::cout << str << std::endl;
}

void Abstract::instructionDispatch(std::string const & str)
{
	void (Abstract::*functptr[])(std::string const & str) = { &Abstract::push, &Abstract::pop, &Abstract::dump, &Abstract::assert, &Abstract::add, &Abstract::sub, &Abstract::mul, &Abstract::div, &Abstract::mod, &Abstract::print, &Abstract::exit } ;
	const std::string instructions[11] = {"push", "pop", "dump", "assert", "add", "sub", "mul", "div", "mod", "print", "exit"};

	for (int i=0;i<str.length();i++){
		if (str.c_str()[i] != ' '){
			for (int x=0;x<11;x++){
				if (strncmp(&str.c_str()[i], instructions[x].c_str(), instructions[x].length()) == 0){
					(this->*functptr[x])(&str.c_str()[i]);
				}
			}
		}
	}
}

Abstract::Abstract(void)
{
	std::cout << "FR4G-TP initialized" << std::endl;
	return ;
}

// Abstract::Abstract(void) : hitPoints(100), maxHitPoints(100), energyPoints(100),
// 	maxEnergyPoints(100), level(1), meleeAttackDamage(30), rangedAttackDamage(20),
// 	armorDamageReduction(5)
// {
// 	std::cout << "FR4G-TP " << " initialized" << std::endl;
// 	return ;
// }

Abstract::Abstract(Abstract const & src)
{
	*this = src;
	// this->hitPoints = src.hitPoints;
	// this->maxHitPoints = src.maxHitPoints;
	// this->energyPoints = src.energyPoints;
	// this->maxEnergyPoints = src.maxEnergyPoints;
	// this->level = src.level;
	// this->meleeAttackDamage = src.meleeAttackDamage;
	// this->rangedAttackDamage = src.rangedAttackDamage;
	// this->armorDamageReduction = src.armorDamageReduction;
	return ;
}

Abstract::~Abstract(void)
{
	// std::cout << "FR4G-TP " << this->name << " destructed" << std::endl;
	return ;
}

Abstract & Abstract::operator=(Abstract const &rhs)
{
	// this->hitPoints = rhs.hitPoints;
	// this->maxHitPoints = rhs.maxHitPoints;
	// this->energyPoints = rhs.energyPoints;
	// this->maxEnergyPoints = rhs.maxEnergyPoints;
	// this->level = rhs.level;
	// this->meleeAttackDamage = rhs.meleeAttackDamage;
	// this->rangedAttackDamage = rhs.rangedAttackDamage;
	// this->armorDamageReduction = rhs.armorDamageReduction;
	return *this;
}

// void Abstract::meleeAttack(std::string const & target)
// {
// 	std::cout << "FR4G-TP " << this->name <<  " attacks " <<  target << " at melee , causing " << this->meleeAttackDamage << " points of damage !" << std::endl;
// }

// void Abstract::takeDamage(unsigned int amount)
// {
// 	if ((this->hitPoints - ((int)amount - this->armorDamageReduction)) < 0)
// 		this->hitPoints = 0;
// 	else
// 		this->hitPoints -= ((int)amount - this->armorDamageReduction);

// 	std::cout << "FR4G-TP " << this->name <<  " took " << amount - this->armorDamageReduction << " points of damage" << std::endl;
// }

// void Abstract::beRepaired(unsigned int amount)
// {
// 	if ((this->hitPoints + (int)amount) > this->maxHitPoints)
// 		this->hitPoints = this->maxHitPoints;
// 	else
// 		this->hitPoints += (int)amount;

// 	std::cout << "FR4G-TP " << this->name <<  " repaired himself for " << amount << " hit points!" << std::endl;

// }

// void Abstract::vaulthunter_dot_exe(std::string const & target)
// {
// 	const char *names[] = {"copies pikachu and uses thunder", "spits with haki", "uses jewjutsu", "starts tapdancing creating an earthquake with the epicenter", "shoots with an Barrett .50 cal m82 "};

// 	if ((this->energyPoints - 25) >= 0)
// 	{
// 		std::cout << "FR4G-TP " << this->name << " " << names[rand() % 5] << " on " << target << std::endl;
// 		this->energyPoints -= 25;
// 	}
// 	else
// 		std::cout << "FR4G-TP " << this->name << " is out of energy" << std::endl;
// }