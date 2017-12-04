# **************************************************************************** #
#                                                                              #
#                                                         :::      ::::::::    #
#    Makefile                                           :+:      :+:    :+:    #
#                                                     +:+ +:+         +:+      #
#    By: tktorza <tktorza@student.42.fr>            +#+  +:+       +#+         #
#                                                 +#+#+#+#+#+   +#+            #
#    Created: 2017/11/14 16:36:20 by tktorza           #+#    #+#              #
#    Updated: 2017/12/04 16:04:06 by tktorza          ###   ########.fr        #
#                                                                              #
# **************************************************************************** #

NAME = woody_woodpacker

SRC_DIR	= ./src

SRC = woody_test.c elf_functions.c main.c

VPATH	= $(SRC_DIR)

O_DIR	= ./obj
OBJ		= $(addprefix $(O_DIR)/,$(SRC:.c=.o))

#OBJ = $(SRC:%.c=obj/%.o)

WFLAGS = #-W -Wall -Werror -Wextra -fPIC

CC = gcc

HEADERS = -I includes/

I_LIBFT = -I libft/includes/

I_PRINTF = libft/ft_printf/libftprintf.a

LIBFT = $(I_LIBFT) -Llibft -lft

all : $(NAME) test

$(NAME): obj $(OBJ)
	@echo "\n";
	@echo "$(CG)	___    __    ____  ______     ______    _______  ____    ____ $(CE)";
	@echo "$(CG)	\   \  /  \  /   / /  __  \   /  __  \  |       \ \   \  /   /$(CE)";
	@echo "$(CG)	 \   \/    \/   / |  |  |  | |  |  |  | |  .--.  | \   \/   /$(CE)";
	@echo "$(CG)	  \            /  |  |  |  | |  |  |  | |  |  |  |  \_    _/$(CE)";
	@echo "$(CG)	   \    /\    /   |   --   | |   --  |  |   --   |    |  |  $(CE)";
	@echo "$(CG)	    \__/  \__/     \______/   \______/  |_______/     |__|$(CE)";
	@echo "\n";
	@echo "\n\033[31m==> COMPILING in progress ...\033[0m\n"
	@make -C libft
	@$(CC) $(WFLAGS) -o $@ $(OBJ) $(HEADERS) $(LIBFT) $(I_PRINTF)
	@echo "\033[1;34mwoody\t\t\033[1;33mCompilation\t\033[0;32m-OK-\033[0m"
	@echo "\n\033[32m==> SUCCESS !\033[0m\n"

obj/%.o: %.c
	@$(CC) $(WFLAGS) $(HEADERS) -o $@ -c $< $(I_LIBFT)

obj:
		@mkdir -p obj/

cp:
	@make clean
	@cp binaries/x86_64-linux-gnu-g++-6 .
	@make
	@./woody_woodpacker x86_64-linux-gnu-g++-6

clean:
	@echo "\n\033[35m==> CLEANING in progress ...\033[0m\n"
	@make clean -C libft
	@rm -rf $(OBJ) x86_84-linux-gnu-g++-6 woody

fclean: clean
	@make fclean -C libft
	@rm -rf $(NAME) libft_malloc*
	@echo "\n\033[32m==> SUCCESS !\033[0m\n"

re: fclean all

norminette:
	@echo "\n"----------------------- NORMINETTE LIBFT --------------------------"\n"
	@norminette libft/*.c libft/includes/*.h
	@echo "\n"----------------------- NORMINETTE MALLOC -------------------------"\n"
	@norminette src/*.c includes/malloc.h
	@echo "\n"--------------------------- END -----------------------------------"\n"

.PHONY: re fclean clean all norminette test