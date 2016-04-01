# **************************************************************************** #
#                                                                              #
#                                                         :::      ::::::::    #
#    Makefile                                           :+:      :+:    :+:    #
#                                                     +:+ +:+         +:+      #
#    By: tktorza <marvin@42.fr>                     +#+  +:+       +#+         #
#                                                 +#+#+#+#+#+   +#+            #
#    Created: 2016/03/23 15:59:45 by tktorza           #+#    #+#              #
#    Updated: 2016/04/01 03:39:52 by tktorza          ###   ########.fr        #
#                                                                              #
# **************************************************************************** #

NAME = fractol

SRC = main.c\
	  initialize.c\
	  fdf_draw_pixel.c\
	  fdf_look.c\
	  lala.c\
	  julia.c\
	  next.c\

OBJ = $(SRC:%.c=%.o)

WFLAGS = -Wall -Werror -Wextra

CC = gcc

HEADERS = -IIncludes/

I_LIBFT = -Ilibft/includes

LIBFT = $(I_LIBFT) -Llibft -lft

I_LIB_OPEN_GL = -I minilibx_macos

LIB_OPEN_GL = $(I_LIB_OPEN_GL) -L minilibx_macos -lmlx -framework OpenGL \
			  -framework AppKit

all: $(NAME)

$(NAME): $(OBJ)
	@make -C libft
	@make -C minilibx_macos
	$(CC) $(WFLAGS) $(HEADERS) $(OBJ) -o $(NAME) $(LIBFT) $(LIB_OPEN_GL)
	
%.o: %.c
	$(CC) $(WFLAGS) $(HEADERS) -c -o $@ $< $(I_LIBFT) $(I_LIB_OPEN_GL)

clean:
	@make clean -C libft
	rm -rf $(OBJ)
	
fclean: clean
	@make fclean -C libft
	@make clean -C minilibx_macos
	rm -rf $(NAME)
	
re: fclean all

.PHONY: re fclean clean all
