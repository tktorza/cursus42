# **************************************************************************** #
#                                                                              #
#                                                         :::      ::::::::    #
#    Makefile                                           :+:      :+:    :+:    #
#                                                     +:+ +:+         +:+      #
#    By: tktorza <tktorza@student.42.fr>            +#+  +:+       +#+         #
#                                                 +#+#+#+#+#+   +#+            #
#    Created: 2017/10/03 12:02:00 by tktorza           #+#    #+#              #
#    Updated: 2017/10/18 14:09:32 by tktorza          ###   ########.fr        #
#                                                                              #
# **************************************************************************** #

NAME_NM = ft_nm

NAME_OTOOL = ft_otool

SRC_NM_DIR	= ./src/nm

SRC_OTOOL_DIR	= ./src/otool

SRC_NM = ft_nm.c sort.c arch_32_style.c arch_64_style.c display_output.c \
	arch_lib_style.c arch_fat_style.c catch_properties.c fill_array.c

SRC_OTOOL = ft_otool.c arch_lib_style.c arch_fat_style.c \
	symtab_building.c handle_o.c catch_properties.c fill_array.c \
	display_by_type_32.c display_by_type_64.c

VPATH	= $(SRC_NM_DIR)

O_DIR = ./obj

OBJ_NM	= $(addprefix $(O_DIR)/,$(SRC_NM:.c=.o))

OBJ_OTOOL = $(addprefix $(O_DIR)/,$(SRC_OTOOL:.c=.o))

WFLAGS =# -W -Wall -Werror -Wextra -fPIC

CC = gcc

HEADERS = -I includes/

I_LIBFT = -I libft/inc/

I_PRINTF = libft/ft_printf/libftprintf.a

LIBFT = $(I_LIBFT) -Llibft -lft

all : $(NAME_NM) $(NAME_OTOOL)

$(NAME_NM): obj $(OBJ_NM)
	@echo "\n"---------------------- NM COMPILING -------------------------
	@echo "\n\033[31m==> COMPILING in progress ...\033[0m\n"
	@make -C libft
	@$(CC) $(WFLAGS) -o $@ $(OBJ_NM) $(HEADERS) $(LIBFT) $(I_PRINTF)
	@echo "\033[37mNM: \033[35mOK\n"
	@echo "\033[32m==> SUCCESS !\033[0m\n"

$(NAME_OTOOL): $(OBJ_OTOOL)
	@echo "\n"---------------------- OTOOL COMPILING -------------------------
	@echo "\n\033[31m==> COMPILING in progress ...\033[0m\n"
	@make -C libft
	@$(CC) $(WFLAGS) -o $@ $(OBJ_OTOOL) $(HEADERS) $(LIBFT) $(I_PRINTF)
	@echo "\033[37mNM: \033[35mOK\n"
	@echo "\033[32m==> SUCCESS !\033[0m\n"

obj:
	@mkdir -p obj/

$(O_DIR)/%.o: $(SRC_NM_DIR)/%.c
	@$(CC) $(WFLAGS) $(HEADERS) -o $@ -c $< $(I_LIBFT)

$(O_DIR)/%.o: $(SRC_OTOOL_DIR)/%.c
	@$(CC) $(WFLAGS) $(HEADERS) -o $@ -c $< $(I_LIBFT)

test:
	@echo "\n"---------------------- tests -------------------------
	@echo "\033[37mNM: \n"
	@echo "\033[37mX64: \n\n"
	@echo "\n\033[32m==> ./ft_nm -p ft_nm > diff1 && nm -p ft_nm > diff2 && diff diff*\033[0m\n"
	@./ft_nm -p ft_nm > diff1 && nm -p ft_nm > diff2 && diff diff*
	@echo "\n\033[32m==> ./ft_nm -p libft/libft.a > diff1 && nm -p libft/libft.a > diff2 && diff diff*\033[0m\n"
	@./ft_nm -p libft/libft.a > diff1 && nm -p libft/libft.a > diff2 && diff diff*
	@echo "\n\033[32m==> ./ft_nm -p obj/ft_nm.o > diff1 && nm -p obj/ft_nm.o > diff2 && diff diff*\033[0m\n"
	@./ft_nm -p obj/ft_nm.o > diff1 && nm -p obj/ft_nm.o > diff2 && diff diff*
	@echo "\n\033[32m==> nm -t d ft_nm > diff2 && ./ft_nm -d ft_nm > diff1 && diff diff*\033[0m\n"
	@nm -t d ft_nm > diff2 && ./ft_nm -d ft_nm > diff1 && diff diff*
	@echo "\n\033[32m==> nm -t d libft/libft.a  > diff2 && ./ft_nm -d libft/libft.a > diff1 && diff diff*\033[0m\n"
	@nm -t d libft/libft.a  > diff2 && ./ft_nm -d libft/libft.a > diff1 && diff diff*
	
clean:
	@echo "\n"----------------------- NM CLEAN ---------------------------
	@echo "\n\033[35m==> CLEANING in progress ...\033[0m\n"
	@rm -rf $(OBJ) $(NAME)

fclean: clean
	@make fclean -C libft
	@echo "\033[35m==> CLEANING test files ...\033[0m\n"
	@rm -rf $(O_DIR)
	@echo ---------------------------- END -------------------------------"\n"

re: fclean all

norminette:
	@echo "\n"----------------------- NORMINETTE LIBFT --------------------------"\n"
	@norminette libft/lib/*.c libft/ft_printf/src/*.c libft/ft_printf/lib/*.c libft/inc/*.h
	@echo "\n"----------------------- NORMINETTE NM -------------------------"\n"
	@norminette src/*.c includes/nm_tool.h
	@echo "\n"--------------------------- END -----------------------------------"\n"

.PHONY: re fclean clean all norminette
