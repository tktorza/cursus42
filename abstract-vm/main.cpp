#include "includes/OperandFactory.hpp"
#include "includes/OperatorControler.hpp"

int main(int ac, char **av)
{
	// std::string ok = av[1];
	// Abstract test;
	// test.instructionDispatch(ok);
	OperandFactory op;

	IOperand un = op.createOperand(Int32, "12321");
	
	return (0);
}