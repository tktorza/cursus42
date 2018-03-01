#ifndef ABSTRACT_H
#define ABSTRACT_H

#include <iostream>
#include <cstdlib>
#include <list>

class Value<char, int, long, float, double>
{
	T type;
	T value;
}

class Abstract 
{
	public:
		// Abstract(/*std::string nam*/);
		Abstract(void);
		Abstract(Abstract const & src);
		Abstract & operator=(Abstract const &rhs);
		~Abstract(void);

		
		// std::string name;
		// int meleeAttackDamage;
		// int rangedAttackDamage;
		// int armorDamageReduction;
		
		char *int8;
		int *int16;
		long *int32;
		float *floater;
		double *doubler;
		int **values; //ex: values[0] = {"doubler", 2}

		void instructionDispatch(std::string const & inst);
		void push(std::string const & str);
		void pop(std::string const & str);
		void dump(std::string const & str);
		void assert(std::string const & str);
		void add(std::string const & str);
		void sub(std::string const & str);
		void mul(std::string const & str);
		void div(std::string const & str);
		void mod(std::string const & str);
		void print(std::string const & str);
		void exit(std::string const & str);
};

#endif