
#include "Parseur.hpp"

class Executioner {
    public:

        Executioner(void);
        Executioner(Executioner const &src); // Copy
        ~Executioner(void);                // Destructeur de recopie
        Executioner &operator=(Executioner const & src);  // operator d'affecationt
  
    void startVm(Parseur *parse);
  
   private:
    std::list<IOperand const *>stack;
    std::list<IOperand const *>::const_iterator start;
    std::list<IOperand const *>::const_iterator end;
  
    eOperandType getEnumId(std:: string type);
    IOperand const * getLast(void);
    IOperand const * getLastAndPop(void);
    void push(eOperandType enumId, std::string const & value);
    void assertE(eOperandType enumId, std::string const & value);
    void add(void);
    void power(void);
    void sub(void);
    void mul(void);
    void div(void);
    void mod(void);
    void print(void);
    void pop(void);
    void dump(void);
    void exitE(void);
    void whileE(IOperand const *last, std::string const & value);
    OperatorFactory factory;
}

Executioner::Executioner(){}

Executioner::vmStart(){
    for (parseur.begin())
}