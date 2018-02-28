#ifndef OPERATERCONTROLER_HPP
# define OPERATERCONTROLER_HPP

#include <iostream>
#include "IOperand.hpp"
#include "operatorFactory.hpp"
#include "errorControler.hpp"
#include <math.h>


template <typename T>
class OperatorController: public IOperand{
    private:
        // long double _max
        // long double _min
        std::string _value
        EIOperandType _type
        OperatorController( std::string & value,  std::string & type) {}

    public:
        int getPrecision( void ) const = 0; // Precision of the type of the instance
        eOperandType getType( void ) const = 0; // Type of the instance

        IOperand const * operator+( IOperand const & rhs ) const = 0; // Sum
        IOperand const * operator-( IOperand const & rhs ) const = 0; // Difference
        IOperand const * operator*( IOperand const & rhs ) const = 0; // Product
        IOperand const * operator/( IOperand const & rhs ) const = 0; // Quotient
        IOperand const * operator%( IOperand const & rhs ) const = 0; // Modulo
        std::string const & toString( void ) const = 0; // String representation of the instance
        ~OperatorController( void ) {}
}

OperatorController::OperatorController( std::string & value,  std::string & type): _value(value), _type(type) {}

IOperand const * operator+( IOperand const & rhs ){
    
}