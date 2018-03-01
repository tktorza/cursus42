#ifndef OPERANDFACTORY_HPP
#define OPERANDFACTORY_HPP

#include <iostream>
#include "IOperand.hpp"

class OperandFactory{
	public:
		OperandFactory(void);
		~OperandFactory(void);
		OperandFactory(OperandFactory const & src);
		OperandFactory & operator=(OperandFactory const &rhs);
		IOperand const * createOperand( eOperandType type, std::string const & value ) const;
	
	private:
		IOperand const * createInt8( std::string const & value ) const;
		IOperand const * createInt16( std::string const & value ) const;
		IOperand const * createInt32( std::string const & value ) const;
		IOperand const * createFloat( std::string const & value ) const;
		IOperand const * createDouble( std::string const & value ) const;
};

#endif //OPERANDFACTORY_HPP
