# **************************************************************************** #
#                                                                              #
#                                                         :::      ::::::::    #
#    Makefile                                           :+:      :+:    :+:    #
#                                                     +:+ +:+         +:+      #
#    By: tktorza <marvin@42.fr>                     +#+  +:+       +#+         #
#                                                 +#+#+#+#+#+   +#+            #
#    Created: 2016/05/24 15:53:26 by tktorza           #+#    #+#              #
#    Updated: 2016/05/24 15:53:28 by tktorza          ###   ########.fr        #
#                                                                              #
# **************************************************************************** #

NAME = lem-in

SRC = 	main.c check.c ft_parsing.c ft_path.c doublon_del.c rev.c init.c\
		delete_maillon.c ft_coporigin.c ft_selection.c ft_algo.c ft_display.c\
		utils.c utils2.c utils3.c utils4.c link_next.c generation.c check_bis.c\

OBJ = $(SRC:%.c=%.o)

WFLAGS = -Wall -Werror -Wextra

CC = gcc

HEADERS = -I includes/

I_LIBFT = -I libft/includes

LIBFT = $(I_LIBFT) -Llibft -lft

all: $(NAME)

$(NAME): $(OBJ)
	@make -C libft
	$(CC) $(WFLAGS) $(HEADERS) $(OBJ) -o $(NAME) $(LIBFT)
	@echo created
	
%.o: %.c
	$(CC) $(WFLAGS) $(HEADERS) -c -o $@ $< $(I_LIBFT)

clean:
	@make clean -C libft
	rm -rf $(OBJ)
	
fclean: clean
	@make fclean -C libft
	rm -rf $(NAME)
	
re: fclean all

.PHONY: re fclean clean all
