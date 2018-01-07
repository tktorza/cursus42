#include "Abstract.hpp"

int main(int ac, char **av)
{
	std::string ok = av[1];
	Abstract test;
	test.instructionDispatch(ok);

	return (0);
}