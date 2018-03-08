# **************************************************************************** #
#                                                                              #
#                                                         :::      ::::::::    #
#    Makefile                                           :+:      :+:    :+:    #
#                                                     +:+ +:+         +:+      #
#    By: tktorza <marvin@42.fr>                     +#+  +:+       +#+         #
#                                                 +#+#+#+#+#+   +#+            #
#    Created: 2017/09/04 14:04:11 by tktorza           #+#    #+#              #
#    Updated: 2017/09/27 10:05:17 by tktorza          ###   ########.fr        #
#                                                                              #
# **************************************************************************** #

SRC = SRC/

S = Sully

JS = JS/

WFLAGS = -Wall -Wextra -Werror

CC = gcc

all : 
	@echo "\n\033[31m==> COMPILING Colleen in progress ...\033[0m\n"
	@$(CC) $(WFLAGS) $(SRC)Colleen.c -o Colleen
	@echo "\n\033[31m==> COMPILING Grace in progress ...\033[0m\n"
	@$(CC) $(WFLAGS) $(SRC)Grace.c -o Grace
	@echo "\n\033[31m==> COMPILING Sully in progress ...\033[0m\n"
	@$(CC) $(WFLAGS) $(SRC)Sully.c -o Sully
	@echo "\033[32m==> SUCCESS !\033[0m\n"

test: all
	@echo "\033[32m ***** TESTS ***** \033[0m\n"
	@echo "\n"
	@echo "\n\033[31m==> Test Colleen\033[0m\n"	
	@echo "diff Colleen.c Colleen.txt"
	@./Colleen > Colleen.txt;diff Colleen.txt $(SRC)Colleen.c
	@echo "\n"
	@echo "\n\033[31m==> Test Grace\033[0m\n"
	@echo "diff Grace.c Grace_kid.c"		
	@./Grace;diff $(SRC)Grace.c Grace_kid.c
	@echo "\n"
	@echo "\n\033[31m==> Test Sully\033[0m\n"
	@./$(S)
	@echo "\n\033[32m ***** END OF TESTS ***** \033[0m\n"

bonus :
	@echo "\033[32m ***** BONUS ***** \033[0m\n"
	@echo "\n"
	@echo "\n\033[31m==> Test Colleen\033[0m\n"
	@echo "diff Colleen.js Colleen.txt"
	@node $(JS)Colleen.js > Colleen.txt;diff Colleen.txt $(JS)Colleen.js
	@echo "\n"
	@echo "\n\033[31m==> Test Grace\033[0m\n"
	@echo "diff Grace.js Grace_kid.js"	
	@node $(JS)Grace.js;diff $(JS)Grace.js Grace_kid.js
	@echo "\n"
	@echo "\n\033[31m==> Test Sully\033[0m\n"		
	@node $(JS)$(S).js
	@echo "\n\033[32m ***** END OF BONUS ***** \033[0m\n"
	

clean:
	@echo "\n\033[31m==> cleaning ...\033[0m\n"
	@ rm -rf Grace_kid.js Grace_kid.c $(S)_* *.txt

fclean: clean
	@rm -rf Sully Grace Colleen

re: fclean all

.PHONY: re fclean clean all bonus testm
