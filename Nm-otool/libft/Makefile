# **************************************************************************** #
#                                                                              #
#                                                         :::      ::::::::    #
#    Makefile                                           :+:      :+:    :+:    #
#                                                     +:+ +:+         +:+      #
#    By: tktorza <marvin@42.fr>                     +#+  +:+       +#+         #
#                                                 +#+#+#+#+#+   +#+            #
#    Created: 2016/02/16 18:26:42 by tktorza           #+#    #+#              #
#    Updated: 2016/03/22 17:17:57 by tktorza          ###   ########.fr        #
#                                                                              #
# **************************************************************************** #

LC			=	ar
LARGS		=	crus

CC			=	gcc
CFLAGS		=	-Wall -Wextra -Werror -c -O

NAME		=	libft.a
OBJDIR		=	./

INCLUDES	=	-I ./includes/

SRCFILES	=	ft_word_number.c\
				ft_strlen.c\
				ft_isalpha.c\
				ft_isdigit.c\
				ft_isalnum.c\
				ft_isascii.c\
				ft_isprint.c\
				ft_toupper.c\
				ft_tolower.c\
				ft_atoi.c\
				ft_itoa.c\
				ft_memalloc.c\
				ft_memset.c\
				ft_memdel.c\
				ft_memmove.c\
				ft_memcpy.c\
				ft_memccpy.c\
				ft_memcmp.c\
				ft_memchr.c\
				ft_bzero.c\
				ft_strnew.c\
				ft_strclr.c\
				ft_strdel.c\
				ft_strdup.c\
				ft_strcpy.c\
				ft_strncpy.c\
				ft_strcat.c\
				ft_strncat.c\
				ft_strlcat.c\
				ft_strchr.c\
				ft_strrchr.c\
				ft_strcmp.c\
				ft_strncmp.c\
				ft_strequ.c\
				ft_strnequ.c\
				ft_strstr.c\
				ft_strnstr.c\
				ft_strsub.c\
				ft_strjoin.c\
				ft_strtrim.c\
				ft_strsplit.c\
				ft_striter.c\
				ft_striteri.c\
				ft_strmap.c\
				ft_strmapi.c\
				ft_putchar.c\
				ft_putstr.c\
				ft_putendl.c\
				ft_putnbr.c\
				ft_putchar_fd.c\
				ft_putstr_fd.c\
				ft_putendl_fd.c\
				ft_putnbr_fd.c\
				ft_lstnew.c\
				ft_lstadd.c\
				ft_lstdelone.c\
				ft_lstdel.c\
				ft_lstiter.c\
				ft_lstmap.c\
				ft_change.c\
				ft_compt.c\
				ft_itoto.c\
				ft_place.c\
				ft_printf.c\
				ft_itoa_base.c\
				ft_itoa_base_bis.c\
				ft_reverse.c\
				ft_anexes.c\
				ft_create_tab.c\
				ft_itoa_diff.c\
				impress_functions.c\
				ft_special.c\
				ft_arg_list.c\
				ft_check.c\
				ft_complication.c\
				ft_diff.c\
				ft_strange.c\
				ft_pourcent.c\
				ft_compt_char.c\
				ft_precision.c\
				ft_qqchose.c\
				ft_anexes_2.c\
				ft_dolls.c\
				ft_printf_anexes_1.c\
				ft_printf_anexes_2.c\
				ft_printf_anexes_3.c\
				impress_functions_2.c\
				impress_functions_3.c\
				ft_false.c\
				ft_arg.c\
				ft_nothing.c\
				ft_color.c\
				get_next_line.c\
				ft_split.c\


COBJS	= $(SRCFILES:%.c=$(OBJDIR)/%.o)

default: $(NAME)
all: default
re: fclean all

$(OBJDIR)/%.o: %.c
	@$(CC) $(CFLAGS) $(INCLUDES) -c $< -o $@

$(NAME): $(COBJS)
	@echo Linking $(NAME)
	@$(LC) $(LARGS) $(NAME) $(COBJS)

clean:
	@echo Cleaning objs..
	@rm -f $(COBJS)

fclean: clean
	@echo Cleaning bin..
	@rm -f $(NAME)

ffclean: clean fclean
	@echo Cleaning _Depend + _Tags..
	@rm -f _Depend _Tags
