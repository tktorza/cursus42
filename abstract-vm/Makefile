EXEC = avm

SRCC = main.cpp OperandFactory.cpp

INCLUDES = includes/OperandFactory.hpp includes/IOperand.hpp

SRCO = $(SRCC:%.cpp=%.o)

FLAG = -Wall -Werror -Wextra

.PHONY: all fast clean fclean

all: main

%.o: %.c
	@echo -n  $< :
	@clang++ -o $@ -c $< $(FLAG)
	@echo

main: $(SRCO)
	@clang++ -o $(EXEC) $(FLAG) $(SRCO) 

fast: $(SRCO)
	@clang++ $(FLAG) -o $(EXEC) $(SRCO)

clean:
	@rm -f $(SRCO)

fclean: clean
	@rm -f $(SRCO) $(EXEC)

re: fclean all