# ifndef PARSEUR_HPP
# define PARSEUR_HPP
#include <string>
#include <list>

#include <iostream>
#include <sstream>
#include <regex>
#include <limits>

struct VM_List
{
  std::string info;
  std::string type;
  std::string value;
};

class Parseur {
public:
  std::list<VM_List> vmList;
  std::list<VM_List>::const_iterator start;
  std::list<VM_List>::const_iterator end;

  Parseur(void);
  Parseur(Parseur const &src); // Copy
  ~Parseur(void); // Destructeur de recopie

  Parseur &operator=(Parseur const & src);  // operator d'affecationt

  int checkeur(std::string & instrucion);
  int lexeur(std::string & instrucion);
  void push(std::string instrucion);
  int getIndexLine(void) const;
  bool exit;

private:
  int _indexLine;
};


#endif