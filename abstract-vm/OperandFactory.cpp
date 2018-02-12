#include "includes/OperandFactory.hpp"

IOperand const * OperandFactory::createInt8( std::string const & value ) const{
		std::cout << "Int8 created" << std::endl;
		return NULL;
}
IOperand const * OperandFactory::createInt16( std::string const & value ) const{
		std::cout << "Int16 created" << std::endl;
		return NULL;
}
IOperand const * OperandFactory::createInt32( std::string const & value ) const{
		std::cout << "Int32 created" << std::endl;
		return NULL;
}
IOperand const * OperandFactory::createFloat( std::string const & value ) const{
		std::cout << "Float created" << std::endl;
		return NULL;
}
IOperand const * OperandFactory::createDouble( std::string const & value ) const{
		std::cout << "Double created" << std::endl;
		return NULL;
}

IOperand const * OperandFactory::createOperand( eOperandType type, std::string const & value ) const{
	std::cout << "Try to create Operand" << std::endl;
	switch(type) {
		case eOperandType::Int8: return createInt8(value);
		case eOperandType::Int16: return createInt16(value);
		case eOperandType::Int32: return createInt32(value);
		case eOperandType::Float: return createFloat(value);
		case eOperandType::Double: return createDouble(value);
	}
	return NULL;
	
}


OperandFactory::OperandFactory(void)
{
	std::cout << "Factory created" << std::endl;
	return ;
}

// OperandFactory::OperandFactory(void) : hitPoints(100), maxHitPoints(100), energyPoints(100),
// 	maxEnergyPoints(100), level(1), meleeAttackDamage(30), rangedAttackDamage(20),
// 	armorDamageReduction(5)
// {
// 	std::cout << "FR4G-TP " << " initialized" << std::endl;
// 	return ;
// }

OperandFactory::OperandFactory(OperandFactory const & src)
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

OperandFactory::~OperandFactory(void)
{
	// std::cout << "FR4G-TP " << this->name << " destructed" << std::endl;
	return ;
}

OperandFactory & OperandFactory::operator=(OperandFactory const &rhs)
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