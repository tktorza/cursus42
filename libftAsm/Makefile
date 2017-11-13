# **************************************************************************** #
#                                                                              #
#                                                         :::      ::::::::    #
#    Makefile                                           :+:      :+:    :+:    #
#                                                     +:+ +:+         +:+      #
#    By: tktorza <marvin@42.fr>                     +#+  +:+       +#+         #
#                                                 +#+#+#+#+#+   +#+            #
#    Created: 2017/11/07 16:05:38 by tktorza           #+#    #+#              #
#    Updated: 2017/11/07 16:07:38 by tktorza          ###   ########.fr        #
#                                                                              #
# **************************************************************************** #

NAME = libfts.a
NAME_TEST = test
PATH_SRC = ./src/

AS = nasm
ASFLAGS = -f macho64
CC = cc -Wall -Wextra -Werror
# CFLAGS = -Ofast -I $(PATH_INC) -g

LIBS = -L ./ -lfts

SRC =   ft_isdigit.s \
		ft_isalpha.s \
		ft_isalnum.s \
		ft_isascii.s \
		ft_isprint.s \
		ft_toupper.s \
		ft_tolower.s \
		ft_bzero.s \
		ft_puts.s \
		ft_put.s \
		ft_strlen.s \
		ft_strcat.s \
		ft_memset.s \
		ft_memcpy.s \
		ft_strdup.s \
		ft_cat.s \

OBJ = $(addprefix $(PATH_SRC), $(SRC:.s=.o))

.PHONY: clean fclean re test

all: $(NAME)

$(NAME): $(OBJ)
	ar rc $(NAME) $(OBJ)
	ranlib $(NAME)

clean:
	/bin/rm -f $(OBJ)

fclean: clean
	/bin/rm -f $(NAME)

re: fclean all

test: all $(OBJ) main.c
	$(CC) main.c $(LIBS) -o $(NAME_TEST)