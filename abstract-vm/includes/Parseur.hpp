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


Parseur::Parseur(void){
  this->exit = false;
  this->_indexLine = 0;
  return;
}

Parseur::Parseur(Parseur const &src) {
  this->_indexLine = src._indexLine;
  this->exit = src.exit;
  return;
}

Parseur &Parseur::operator=(Parseur const & src) {
  (void)(src);

  return *this;
}


Parseur::~Parseur(void) {
   return;
}

int Parseur::getIndexLine(void) const {
  return this->_indexLine;
}

int Parseur::checkeur(std::string & instrucion) {
  this->_indexLine = this->_indexLine + 1;
  std::regex elRegex("(push (?![ ]{1,})|pop|dump|assert (?![ ]{1,})|add|sub|mul|div|mod|print|pow|exit|while (\\([0-9]*\\)))((?=\\n|$)|int8(\\([0-9]*\\)|\\(-[0-9]*\\))|int16(\\([0-9]*\\)|\\(-[0-9]*\\))|int32(\\([0-9]*\\)|\\(-[0-9]*\\))|float((\\([0-9]*\\)|\\(-[0-9]*\\))|(\\(-\\d+(\\.[0-9]\\d*?\\))|\\.[0-9]\\d+|\\(\\d+(\\.[0-9]\\d*?\\))|\\.[0-9]\\d+))|double((\\([0-9]*\\)|\\(-[0-9]*\\))|(\\(-\\d+(\\.[0-9]\\d*?\\))|\\.[0-9]\\d+|\\(\\d+(\\.[0-9]\\d*?\\))|\\.[0-9]\\d+)))");
  std::stringstream split;
  char char_split = ';';
  split << instrucion;

  std::getline(split, instrucion, char_split);
  if(instrucion.empty()) { return 0; }
  if (regex_match(instrucion, elRegex) && instrucion != "exit") {
    return 0;
  } else if (instrucion == "exit") {
    this->exit = true;
    this->end = this->vmList.end();
    this->start = this->vmList.begin();
    return 0;
  }
  return 1;
}

int Parseur::lexeur(std::string & instrucion) {
  
    size_t pos = 0;
    std::string info;
    std::string type;
    VM_List tmp;
    std::string delimiter = " ";
    while ((pos = instrucion.find(delimiter)) != std::string::npos) {
        info = instrucion.substr(0, pos);
        instrucion.erase(0, pos + delimiter.length());
    }
      if (info != "" && instrucion != "") {
        pos = 0;
        delimiter= "(";
        while ((pos = instrucion.find(delimiter)) != std::string::npos) {
            type = instrucion.substr(0, pos);
            instrucion.erase(0, pos + delimiter.length());
            instrucion = instrucion.substr(0, instrucion.size()-1);
        }
        tmp.info = info;
        tmp.type = type;
        tmp.value = instrucion;
        if (instrucion.empty()){ throw std::logic_error("\033[1;31mThis instruction is false: value empty \033[0m");}
        long double value = std::stold(instrucion);
        if (tmp.type == "int8" && (value <  CHAR_MIN || value > CHAR_MAX)) { return 3; }
        if (tmp.type == "int16" && (value <  SHRT_MIN || value > SHRT_MAX)) { return 3; }
        if (tmp.type == "int32" && (value <  INT_MIN || value > INT_MAX )) { return 3; }
        if (tmp.type == "float" && (value < std::numeric_limits<float>::lowest() || value > std::numeric_limits<float>::max() )) { return 3; }
        if (tmp.type == "double" && (value < std::numeric_limits<double>::lowest() || value > std::numeric_limits<double>::max() )) { return 3; }
      } else {
        tmp.type = "null";
        tmp.value = "null";
        tmp.info = instrucion;
      }
  
      this->vmList.push_back(tmp);
    return 0;
  }

#endif