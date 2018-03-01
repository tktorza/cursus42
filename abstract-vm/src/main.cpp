#include "../includes/OperandFactory.hpp"
#include "../includes/ErrorControler.hpp"
#include "../includes/Executioner.hpp"
#include "../includes/OperatorControler.hpp"
#include <iostream>
#include <fstream>
#include <string>
#include <list>

int main(int argc, char **argv)
{
	OperandFactory op;
	std::string lineFd = "";
	std::string nextLine = "";
	Parseur *parse = new Parseur();
	ErrorControler erreur;
	Executioner exec;
	int stop = 0;
	
	if (argc > 1){
		try{
			std::ifstream fd;
			fd.open(argv[1]);
			if(!fd.is_open()) { std::cout <<"\033[1;33mWow !!! we can't open this type of file !!\033[0m\n"; return 0; }
			while (!fd.eof()){
				std::getline(fd, lineFd);
				//check 
				if (parse->exit) { nextLine = lineFd; }
				if ((erreur.needToStopFd(parse->checkeur(lineFd), parse->exit, nextLine, !fd.eof(), parse->getIndexLine()) == 1 ||  erreur.needToStopFd(parse->lexeur(lineFd), parse->exit, nextLine, !fd.eof(), parse->getIndexLine()) == 1) && !fd.eof() != 0) {
				  std::cout <<"\033[1;33m" << lineFd << "\033[0m\n";
				  stop = 1;
				}
			  }
				if (stop == 1) {
				  fd.close();
				  return 0;
				}
			  erreur.endofFile(!parse->exit);
		  
				fd.close();
				exec.startVm(parse);
			  } catch ( const std::exception & e ) {
				std::cerr << e.what();
			}
		}else{
			try {
				for (std::string line; std::getline(std::cin, line);) {
					if (";;" == line) {
						if (!parse->exit) { throw std::logic_error( "you miss exit"); }
						break;
					}
					if (parse->exit) { nextLine = line; }
					if ((erreur.needToStopCin(parse->checkeur(line), parse->exit, nextLine, parse->getIndexLine()) == 1 || erreur.needToStopCin(parse->lexeur(line), parse->exit, nextLine, parse->getIndexLine()) == 1)) {
						std::cout << line << '\n';
						return 0;
					}
			}
				exec.startVm(parse);
			} catch (const std::exception & e) {
				std::cerr << e.what();
			}
		}
		return 0;
}