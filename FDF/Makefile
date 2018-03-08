# **************************************************************************** #
#                                                                              #
#                                                         :::      ::::::::    #
#    Makefile                                           :+:      :+:    :+:    #
#                                                     +:+ +:+         +:+      #
#    By: tktorza <marvin@42.fr>                     +#+  +:+       +#+         #
#                                                 +#+#+#+#+#+   +#+            #
#    Created: 2016/03/22 15:55:06 by tktorza           #+#    #+#              #
#    Updated: 2016/03/22 17:53:49 by tktorza          ###   ########.fr        #
#                                                                              #
# **************************************************************************** #

NAME = fdf

SRC = 	srcs/fdf_convert_iso.c \
		srcs/fdf_draw_pixel.c \
		srcs/fdf_error.c \
		srcs/fdf_expose_hook.c \
		srcs/fdf_find_point.c \
		srcs/fdf_look.c \
		srcs/fdf_trace.c \
		srcs/ft_parsing.c \

OBJ = $(SRC:%.c=%.o)

WFLAGS = -Wall -Werror -Wextra

CC = gcc

HEADERS = -Iincludes/

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
