# **************************************************************************** #
#                                                                              #
#                                                         :::      ::::::::    #
#    Makefile                                           :+:      :+:    :+:    #
#                                                     +:+ +:+         +:+      #
#    By: tktorza <tktorza@student.42.fr>          +#+  +:+       +#+         #
#                                                 +#+#+#+#+#+   +#+            #
#    Created: 2015/11/28 10:56:39 by tktorza          #+#    #+#              #
#    Updated: 2017/10/18 13:44:16 by tktorza          ###   ########.fr        #
#                                                                              #
# **************************************************************************** #

.PHONY: all clean fclean re

NAME = libft.a

CC = gcc

CFLAGS = -Wall -Werror -Wextra

LIB_PATH = ./lib
OBJLIB_PATH = ./obj

LIB_NAME =	ft_memset.c ft_bzero.c ft_memcpy.c ft_memccpy.c ft_memmove.c \
			ft_memchr.c ft_memcmp.c ft_strlen.c ft_strdup.c ft_strcpy.c \
			ft_strncpy.c ft_strcat.c ft_strncat.c ft_strlcat.c ft_strchr.c \
			ft_strrchr.c ft_strstr.c ft_strnstr.c ft_strcmp.c ft_strncmp.c \
			ft_atoi.c ft_isalpha.c ft_isdigit.c ft_isalnum.c ft_isascii.c \
			ft_isprint.c ft_toupper.c ft_tolower.c ft_memalloc.c ft_memdel.c \
			ft_strnew.c ft_strdel.c ft_strclr.c ft_striter.c ft_striteri.c \
			ft_strmap.c ft_strmapi.c ft_strequ.c ft_strnequ.c ft_strsub.c \
			ft_strjoin.c ft_strtrim.c ft_strsplit.c ft_itoa.c ft_putchar.c \
			ft_putstr.c ft_putendl.c ft_putnbr.c ft_putchar_fd.c ft_swap.c \
			ft_putstr_fd.c ft_putendl_fd.c ft_putnbr_fd.c ft_strrev.c \
			ft_str_delwhite.c ft_isupper.c ft_islower.c ft_lstadd.c \
			ft_strocpy.c get_next_line.c ft_putstrn.c ft_itoa_base.c \
			ft_itohex.c

OBJLIB_NAME = $(LIB_NAME:.c=.o)

LIB = $(addprefix $(LIB_PATH)/, $(LIB_NAME))
OBJLIB = $(addprefix $(OBJLIB_PATH)/,$(OBJLIB_NAME))

all: $(NAME)
	@cd ./ft_printf && $(MAKE)

$(NAME): $(OBJLIB)
	@ar rc	$(NAME) $(OBJLIB)
	@ranlib	$(NAME)
	@echo "	_,  _ __, __, ___"
	@echo "	|   | |_) |_   | "
	@echo "	| , | |_) |    | "
	@echo "	~~~ ~ ~   ~    ~ "
	@echo "\033[1;34mLibft\t\t\033[1;33mCompilation\t\033[0;32m-OK-\033[0m"

$(OBJLIB_PATH)/%.o: $(LIB_PATH)/%.c
	@mkdir $(OBJLIB_PATH) 2> /dev/null || true
	@$(CC) $(CFLAGS) -o $@ -c $<

clean:
	@rm -rf $(OBJLIB)
	@echo "\033[1;34mLibft\t\t\033[1;33mCleaning obj\t\033[0;32m-OK-\033[0m"

fclean: clean
	@rm -rf ./obj $(NAME)
	@echo "\033[1;34mLibft\t\t\033[1;33mCleaning lib\t\033[0;32m-OK-\033[0m"
	@cd ./ft_printf && $(MAKE) fclean

re: fclean all

norme:
	@norminette $(LIB) $(INC)
	@echo "\033[1;34mLibft\t\t\033[1;33mNorminette\t\033[0;32m-OK-\033[0m"
	@cd ./ft_printf && $(MAKE) norme
