NAME 						= fractol
CC							= gcc
H_DIR 						= includes/
C_DIR 						= srcs/
SUB_DIR 					= srcs/hook/
SUB_DIR1 					= srcs/draw/
SUB_DIR2 					= srcs/get_info/

LIBS						= -lmlx -L/usr/local/lib/ -I/usr/local/include  -framework OpenGL -framework AppKit

O_DIR 						= o/
LIBFT 						= libft/


FLAGS =  -O3 -Wall -Werror -Wextra
LINKS = -I$(H_DIR) -I$(LIBFT)

C_FILES = $(shell ls -1 $(C_DIR) $(SUB_DIR) $(SUB_DIR1) $(SUB_DIR2)  | grep "\.c")

O_FILES = $(addprefix $(O_DIR),$(C_FILES:.c=.o))

all:
	@make -C $(LIBFT);
	@make -j4 $(NAME);

$(NAME): $(O_FILES)
	@$(CC) $(CFLAGS) $(LIBS) $(O_FILES) $(FT_O) $(LIBFT)libft.a -o $(NAME) && printf "\033[0;32m" || printf "\033[0;31m"
	@printf "%-24s\033[1;30m<<--\033[0;0m\n" "$@"

$(O_DIR)%.o: $(C_DIR)%.c
	@mkdir $(O_DIR) 2> /dev/null || echo "" > /dev/null
	@$(CC) $(FLAGS) $(LINKS) -o $@ -c $< \
	&& printf "\033[0;0m%-24s\033[1;30m-->>	\033[0;32m$@\033[0;0m\n" "$<" \
	|| (printf "\033[0;0m%-24s\033[1;30m-->>	\033[0;31m$@\033[0;0m\n" "$<" \
		&& exit 1)

$(O_DIR)%.o: $(SUB_DIR)%.c
	@mkdir $(O_DIR) 2> /dev/null || echo "" > /dev/null
	@$(CC) $(FLAGS) $(LINKS) -o $@ -c $< \
	&& printf "\033[0;0m%-24s\033[1;30m-->>	\033[0;32m$@\033[0;0m\n" "$<" \
	|| (printf "\033[0;0m%-24s\033[1;30m-->>	\033[0;31m$@\033[0;0m\n" "$<" \
	&& exit 1)

$(O_DIR)%.o: $(SUB_DIR1)%.c
	@mkdir $(O_DIR) 2> /dev/null || echo "" > /dev/null
	@$(CC) $(FLAGS) $(LINKS) -o $@ -c $< \
	&& printf "\033[0;0m%-24s\033[1;30m-->>	\033[0;32m$@\033[0;0m\n" "$<" \
	|| (printf "\033[0;0m%-24s\033[1;30m-->>	\033[0;31m$@\033[0;0m\n" "$<" \
	&& exit 1)

$(O_DIR)%.o: $(SUB_DIR2)%.c
	@mkdir $(O_DIR) 2> /dev/null || echo "" > /dev/null
	@$(CC) $(FLAGS) $(LINKS) -o $@ -c $< \
	&& printf "\033[0;0m%-24s\033[1;30m-->>	\033[0;32m$@\033[0;0m\n" "$<" \
	|| (printf "\033[0;0m%-24s\033[1;30m-->>	\033[0;31m$@\033[0;0m\n" "$<" \
	&& exit 1)

clean:
	@rm $(O_FILES) $(O_DIR2) 2> /dev/null || echo "" > /dev/null
	@rmdir -p $(O_DIR) $(O_DIR2) 2> /dev/null || echo "" > /dev/null
	@make -C $(LIBFT) clean

fclean: clean
	@rm $(NAME) 2> /dev/null || echo "" > /dev/null
	@make -C $(LIBFT) fclean

re: fclean all
.PHONY: all clean fclean re
