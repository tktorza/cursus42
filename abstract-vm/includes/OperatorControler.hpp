#ifndef OPERATORCONTROLER_HPP
#define OPERATORCONTROLER_HPP

#include "IOperand.hpp"
#include "OperandFactory.hpp"
#include "ErrorControler.hpp"
#include <iostream>
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

#endif //OPERATORCONTROLER_HPP
