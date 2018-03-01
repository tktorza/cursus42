#include "../includes/OperandFactory.hpp"
#include "../includes/OperatorControler.hpp"

OperandFactory::OperandFactory( void ) {}
OperandFactory::OperandFactory( OperandFactory const & obj ) { static_cast<void>(obj); }
OperandFactory::~OperandFactory( void ) {return;}


OperandFactory & OperandFactory::operator=( OperandFactory const & rhs ) { static_cast<void>(rhs); return *this; }

IOperand const * OperandFactory::createOperand(eOperandType type, std::string const & value) const
{
	switch(type) {
		case eOperandType::Int8: return createInt8(value);
		case eOperandType::Int16: return createInt16(value);
		case eOperandType::Int32: return createInt32(value);
		case eOperandType::Float: return createFloat(value);
		case eOperandType::Double: return createDouble(value);
	}
	return NULL;
}

IOperand const * OperandFactory::createInt8(std::string const & value) const {
	return new OperatorControler<int8_t>(std::stoi(value), eOperandType::Int8, CHAR_MAX, CHAR_MIN);
}

IOperand const * OperandFactory::createInt16(std::string const & value) const {
	return new OperatorControler<int16_t>(std::stoi(value), eOperandType::Int16, SHRT_MAX, SHRT_MIN);
}

IOperand const * OperandFactory::createInt32(std::string const & value) const {
	return new OperatorControler<int32_t>(std::stoi(value), eOperandType::Int32, INT_MAX, INT_MIN);
}

IOperand const * OperandFactory::createFloat(std::string const & value) const {
	return new OperatorControler<float>(std::stof(value), eOperandType::Float,std::numeric_limits<float>::max(), std::numeric_limits<float>::lowest());
}

IOperand const * OperandFactory::createDouble(std::string const & value) const {
	return new OperatorControler<double>(std::stod(value), eOperandType::Double, std::numeric_limits<double>::max(),std::numeric_limits<double>::lowest());
}
