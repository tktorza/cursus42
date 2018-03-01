EXEC = bin/avm

INC	= -I includes

SRC = main.cpp ErrorControler.cpp Executioner.cpp OperandFactory.cpp OperatorControler.cpp Parseur.cpp
OBJ	= $(SRC:%.cpp=obj/%.o)

COMPILE = g++ -std=c++11
FLAG = -Wall -Werror -Wextra

all: $(EXEC)

$(EXEC): $(OBJ)
	$(COMPILE) $(FLAG) -o $@ $(OBJ) $(LIB)

%.o: src/%.cpp
	@echo -n  $< :
	@clang++ -o $@ -c $< $(FLAG)
	@echo

obj/%.o: src/%.cpp
		$(COMPILE) $(FLAG) $(INC) -o $@ -c $<

clean:
		rm -fr obj
		mkdir obj
		touch obj/.gitkeep

fclean: clean
		rm -fr bin
		mkdir bin
		touch bin/.gitkeep

re: fclean all