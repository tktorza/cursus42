#include "../includes/OperatorControler.hpp"

template<typename T> OperatorControler<T>::OperatorControler(void) {
	this->_value = std::to_string(static_cast<T>(0));
	this->_type = eOperandType::Double;
}
template<typename T> OperatorControler<T>::OperatorControler(T value, eOperandType type, long double max, long double min) {
	this->_value = std::to_string(value);
	this->_type = type;
	this->_max = max;
	this->_min = min;
}
template<typename T> OperatorControler<T>::OperatorControler(OperatorControler<T> const &src) {
	this->_value = src->_value;
	this->_type = src->_type;
	this->_max = src->_max;
	return this;
}

template<typename T> OperatorControler<T>::~OperatorControler(void) {
	return;
}

template<typename T> OperatorControler<T> & OperatorControler<T>::operator=(OperatorControler<T> const &rhs)
{
	this->_value = rhs._value;
	this->_type = rhs._type;
	this->_max = rhs._max;
	this->_min = rhs._min;
	this->getPrecision = rhs.getPrecision;
	this->getMax = rhs.getMax;
	this->getMin = rhs.getMin;
	this->getType = rhs.getType;
	this->toString = rhs.toString;
	this->level = rhs.level;
	return *this;
}

template <typename T> IOperand const * OperatorControler<T>::operator+(IOperand const & rhs) const {
	OperandFactory factory;
	ErrorControler error;
	
	std::string type = "add";
	IOperand const * val = NULL;

	if (this->getPrecision() < rhs.getPrecision()){
		error.overflow(&rhs, this, rhs.getMax(), type);
		error.underflow(&rhs, this, rhs.getMin(), type);
		val = factory.createOperand(rhs.getType(), std::to_string(std::stod(this->_value) + std::stod(rhs.toString())));
	}else{
		error.overflow(&rhs, this, this->_max, type);
		error.underflow(&rhs, this, this->_min, type);
		val = factory.createOperand(this->_type, std::to_string(std::stod(this->_value) + std::stod(rhs.toString())));
	}
	return val;
}

template <typename T> IOperand const * OperatorControler<T>::operator-(IOperand const & rhs) const {
	
	OperandFactory factory;
	ErrorControler error;
	std::string type = "sub";
	IOperand const * val = NULL;

	if (this->getPrecision() < rhs.getPrecision()){
		error.overflow(&rhs, this, rhs.getMax(), type);
		error.underflow(&rhs, this, rhs.getMin(), type);
		val = factory.createOperand(rhs.getType(), std::to_string(std::stod(this->_value) - std::stod(rhs.toString())));
	}else{
		error.overflow(&rhs, this, this->_max, type);
		error.underflow(&rhs, this, this->_min, type);
		val = factory.createOperand(this->_type, std::to_string(std::stod(this->_value) - std::stod(rhs.toString())));
	}
	return val;
}

template <typename T> IOperand const * OperatorControler<T>::operator*(IOperand const & rhs) const {
	
	OperandFactory factory;
	ErrorControler error;
	std::string type = "mul";
	IOperand const * val = NULL;

	if (this->getPrecision() < rhs.getPrecision()){
		error.overflow(&rhs, this, rhs.getMax(), type);
		error.underflow(&rhs, this, rhs.getMin(), type);
		val = factory.createOperand(rhs.getType(), std::to_string(std::stod(this->_value) * std::stod(rhs.toString())));
	}else{
		error.overflow(&rhs, this, this->_max, type);
		error.underflow(&rhs, this, this->_min, type);
		val = factory.createOperand(this->_type, std::to_string(std::stod(this->_value) * std::stod(rhs.toString())));
	}
	return val;
}

template <typename T> IOperand const * OperatorControler<T>::operator/(IOperand const & rhs) const {
	
	OperandFactory factory;
	ErrorControler error;
	std::string type = "div";
	IOperand const * val = NULL;

	if (this->getPrecision() < rhs.getPrecision()){
		error.overflow(&rhs, this, rhs.getMax(), type);
		error.underflow(&rhs, this, rhs.getMin(), type);
		val = factory.createOperand(rhs.getType(), std::to_string(std::stod(this->_value) / std::stod(rhs.toString())));
	}else{
		error.overflow(&rhs, this, this->_max, type);
		error.underflow(&rhs, this, this->_min, type);
		val = factory.createOperand(this->_type, std::to_string(std::stod(this->_value) / std::stod(rhs.toString())));
	}
	return val;
}

template <typename T> IOperand const * OperatorControler<T>::operator%(IOperand const & rhs) const {
	
	OperandFactory factory;
	ErrorControler error;
	IOperand const * retVal = NULL;

	if(this->getPrecision() < rhs.getPrecision()) {
		error.overflow(&rhs, this, rhs.getMax(), "mod");
		error.underflow(&rhs, this, rhs.getMin(), "mod");
		retVal =factory.createOperand(rhs.getType(), std::to_string(std::stoi(this->_value) % std::stoi(rhs.toString())));
	} else {
		error.overflow(&rhs, this, this->_max, "mod");
		error.underflow(&rhs, this, this->_min, "mod");
		retVal = factory.createOperand(this->_type, std::to_string(std::stoi(this->_value) % std::stoi(rhs.toString())));
	}
	return retVal;
}

template <typename T> IOperand const * OperatorControler<T>::operator^(IOperand const & rhs) const {
	
	OperandFactory factory;
	ErrorControler error;
	std::string type = "pow";
	IOperand const * val = NULL;

	if (this->getPrecision() < rhs.getPrecision()){
		error.overflow(&rhs, this, rhs.getMax(), type);
		error.underflow(&rhs, this, rhs.getMin(), type);
		val = factory.createOperand(rhs.getType(), std::to_string(pow(std::stod(this->_value), std::stod(rhs.toString()))));
	}else{
		error.overflow(&rhs, this, this->_max, type);
		error.underflow(&rhs, this, this->_min, type);
		val = factory.createOperand(this->_type, std::to_string(pow(std::stod(this->_value), std::stod(rhs.toString()))));
	}
	return val;
}

template<typename T> int	OperatorControler<T>::getPrecision( void ) const {
	return static_cast<int>(this->_type);
}

template<typename T> eOperandType OperatorControler<T>::getType( void ) const {
	return this->_type;
}

template<typename T> long double OperatorControler<T>::getMax( void ) const {
	return this->_max;
}
template<typename T> long double OperatorControler<T>::getMin( void ) const {
	return this->_min;
}
template<typename T> std::string const & OperatorControler<T>::toString( void ) const {
	return this->_value;
}
