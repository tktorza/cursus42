#ifndef OPERATORCONTROLER_HPP
#define OPERATORCONTROLER_HPP

#include <iostream>
#include "IOperand.hpp"
#include "OperandFactory.hpp"
#include "errorControler.hpp"
#include <math.h>

template <typename T> class OperatorControler : public IOperand {
	private:
		std::string _value;
	 	eOperandType _type;
		long double _max;
		long double _min;

	public:
		OperatorControler(void);
		OperatorControler(T value, eOperandType type, long double max, long double min);
		OperatorControler(OperatorControler const &src);
		~OperatorControler(void);

		OperandFactory factory;
		ErrorControler error;
		int	getPrecision( void ) const ;
		long double getMax(void) const;
		long double getMin(void) const;
		IOperand const * operator+(IOperand const & rhs) const;
		IOperand const * operator-(IOperand const & rhs) const;
		IOperand const * operator*(IOperand const & rhs) const;
		IOperand const * operator/(IOperand const & rhs) const;
		IOperand const * operator%(IOperand const & rhs) const;
		IOperand const * operator^(IOperand const & rhs) const;
		OperatorControler & operator=(OperatorControler const &rhs);

		std::string const & toString( void ) const; // String representation of the instance
		eOperandType getType( void ) const; // Type of the instance
};

template<typename T> OperatorControler<T>::OperatorControler(void) {
	this->_value = std::to_string(static_cast<T>(0));
	this->_type = eOperandType::enum_double;
}
template<typename T> OperatorControler<T>::OperatorControler(T value, eOperandType type, long double max, long double min) {
	this->_value = std::to_string(value);
	this->_type = type;
	this->_max = max;
	this->_min = min;
}
template<typename T> OperatorControler<T>::OperatorControler(OperatorControler const &src) {
	this->_value = src->_value;
	this->_type = src->_type;
	this->_max = src->_max;
	return this;
}

template<typename T> OperatorControler<T>::~OperatorControler(void) {
	return;
}

OperatorControler & OperatorControler::operator=(OperatorControler const &rhs)
{
	this->_value = rhs._value;
	this->_type = rhs._type;
	this->_max = rhs._max;
	this->_min = rhs._min;
	this->factory = rhs.factory;
	this->error = rhs.error;
	this->getPrecision = rhs.getPrecision;
	this->getMax = rhs.getMax;
	this->getMin = rhs.getMin;
	this->getType = rhs.getType;
	this->toString = rhs.toString;
	this->level = rhs.level;
	return *this;
}

template <typename T> IOperand const * OperatorControler<T>::operator+(IOperand const & rhs) const {
	std::string type = "add";
	IOperand const * val = NULL;

	if (this.getPrecision() < rhs.getPrecision()){
		this.error.overflow(&rhs, this, rhs.getMax(), type);
		this.error.underflow(&rhs, this, rhs.getMin(), type);
		val = this.factory.createOperand(rhs.getType(), std::to_string(std::stod(this._value) + std::stod(rhs.toString())));
	}else{
		this.error.overflow(&rhs, this, this._max, type);
		this.error.underflow(&rhs, this, this._min, type);
		val = this.factory.createOperand(this._type, std::to_string(std::stod(this._value) + std::stod(rhs.toString())));
	}
	return val;
}

template <typename T> IOperand const * OperatorControler<T>::operator-(IOperand const & rhs) const {
	std::string type = "sub";
	IOperand const * val = NULL;

	if (this.getPrecision() < rhs.getPrecision()){
		this.error.overflow(&rhs, this, rhs.getMax(), type);
		this.error.underflow(&rhs, this, rhs.getMin(), type);
		val = this.factory.createOperand(rhs.getType(), std::to_string(std::stod(this._value) - std::stod(rhs.toString())));
	}else{
		this.error.overflow(&rhs, this, this._max, type);
		this.error.underflow(&rhs, this, this._min, type);
		val = this.factory.createOperand(this._type, std::to_string(std::stod(this._value) - std::stod(rhs.toString())));
	}
	return val;
}

template <typename T> IOperand const * OperatorControler<T>::operator*(IOperand const & rhs) const {
	std::string type = "mul";
	IOperand const * val = NULL;

	if (this.getPrecision() < rhs.getPrecision()){
		this.error.overflow(&rhs, this, rhs.getMax(), type);
		this.error.underflow(&rhs, this, rhs.getMin(), type);
		val = this.factory.createOperand(rhs.getType(), std::to_string(std::stod(this._value) * std::stod(rhs.toString())));
	}else{
		this.error.overflow(&rhs, this, this._max, type);
		this.error.underflow(&rhs, this, this._min, type);
		val = this.factory.createOperand(this._type, std::to_string(std::stod(this._value) * std::stod(rhs.toString())));
	}
	return val;
}

template <typename T> IOperand const * OperatorControler<T>::operator/(IOperand const & rhs) const {
	std::string type = "div";
	IOperand const * val = NULL;

	if (this.getPrecision() < rhs.getPrecision()){
		this.error.overflow(&rhs, this, rhs.getMax(), type);
		this.error.underflow(&rhs, this, rhs.getMin(), type);
		val = this.factory.createOperand(rhs.getType(), std::to_string(std::stod(this._value) / std::stod(rhs.toString())));
	}else{
		this.error.overflow(&rhs, this, this._max, type);
		this.error.underflow(&rhs, this, this._min, type);
		val = this.factory.createOperand(this._type, std::to_string(std::stod(this._value) / std::stod(rhs.toString())));
	}
	return val;
}

template <typename T> IOperand const * OperatorControler<T>::operator%(IOperand const & rhs) const {
	std::string type = "mod";
	IOperand const * val = NULL;

	if (this.getPrecision() < rhs.getPrecision()){
		this.error.overflow(&rhs, this, rhs.getMax(), type);
		this.error.underflow(&rhs, this, rhs.getMin(), type);
		val = this.factory.createOperand(rhs.getType(), std::to_string(std::stod(this._value) % std::stod(rhs.toString())));
	}else{
		this.error.overflow(&rhs, this, this._max, type);
		this.error.underflow(&rhs, this, this._min, type);
		val = this.factory.createOperand(this._type, std::to_string(std::stod(this._value) % std::stod(rhs.toString())));
	}
	return val;
}

template <typename T> IOperand const * OperatorControler<T>::operator^(IOperand const & rhs) const {
	std::string type = "pow";
	IOperand const * val = NULL;

	if (this.getPrecision() < rhs.getPrecision()){
		this.error.overflow(&rhs, this, rhs.getMax(), type);
		this.error.underflow(&rhs, this, rhs.getMin(), type);
		val = this.factory.createOperand(rhs.getType(), std::to_string(pow(std::stod(this._value), std::stod(rhs.toString()))));
	}else{
		this.error.overflow(&rhs, this, this._max, type);
		this.error.underflow(&rhs, this, this._min, type);
		val = this.factory.createOperand(this._type, std::to_string(pow(std::stod(this._value), std::stod(rhs.toString()))));
	}
	return val;
}

int	getPrecision( void ) const {
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

#endif //OPERATORCONTROLER_HPP
