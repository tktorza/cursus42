# ifndef ERRORCONTROLER_HPP
# define ERRORCONTROLER_HPP

#include <string>
#include <iostream>
#include "Parseur.hpp"
#include "IOperand.hpp"
#include <math.h>


#define EndOF "You must have an exit at the end of instructions"
#define SYNTAX_ERROR "Syntax error on this instruction: "
#define EXIT_ERROR "Exit must be the last instruction: "
#define FALSE_INST_ERROR "This instruction is false: "

class ErrorControler
{
  public:

    ErrorControler(void);
    ErrorControler(ErrorControler const &src); // Copy
    ~ErrorControler(void);                // Destructeur de recopie

    ErrorControler &operator=(ErrorControler const & src);  // operator d'affecationt
    int needToStopFd(int type, bool isExit, std::string nextValue, bool fd, int line);
    int needToStopCin(int type, bool isExit, std::string nextValue, int line);
    void endofFile(bool isExit);
    void overflow(IOperand const *operan, IOperand const *operan1, long double max, std::string opertionType);
    void underflow(IOperand const *operan, IOperand const *operan1, long double min, std::string opertionType);
    bool exit;

  private:
    int putError(int line, std::string const & error) const;
};

#endif