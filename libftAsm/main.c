/* ************************************************************************** */
/*                                                                            */
/*                                                        :::      ::::::::   */
/*   main.c                                             :+:      :+:    :+:   */
/*                                                    +:+ +:+         +:+     */
/*   By: tktorza <marvin@42.fr>                     +#+  +:+       +#+        */
/*                                                +#+#+#+#+#+   +#+           */
/*   Created: 2017/11/07 16:12:14 by tktorza           #+#    #+#             */
/*   Updated: 2017/11/07 16:12:15 by tktorza          ###   ########.fr       */
/*                                                                            */
/* ************************************************************************** */

#include <stdio.h>
#include <stdlib.h>
#include "includes/libfts.h"
#include <strings.h>
#include <unistd.h>
#include <fcntl.h>

void	test_bzero()
{
	const char *s = "dewdewde";

	printf("=== TEST ft_bzero ===\n");
	
		char test[] = "salut";
		int i;
		printf("String test = salut\n");
		for (i = 0; i < 5; i++){
			printf("%d\n", test[i]);
		}
		printf("calling ft_bzero(void *, size_t)\n");
		ft_bzero(test, 5);
		for (i = 0; i < 5; i++){
			printf("%d\n", test[i]);
		}
		printf("=== end ft_bzero ===\n");

		printf("PUTS\n");
		ft_puts(s);
		printf("\nputs end\n");
		printf("%d\n", ft_put("a"));
		printf("%d\n", ft_put("a"));
		printf("%d\n", ft_put("a"));
		printf("%d\n", ft_put("a"));
		printf("%d\n", ft_put("a"));
}

void 	test_strlen()
{
	char s[200] = "aa";
	 char s2[100] = "zzz\0";
	// ft_puts(s);
	printf("STRCAT: %s\n", ft_strcat(s, s2));
	printf("STRCAT: %s\n", ft_strcat(s, s2));
	printf("STRCAT: %s\n", ft_strcat(s, s2));
	printf("STRCAT: %s\n", ft_strcat(s, s2));
	printf("STRCAT: %s\n", ft_strcat(s, s2));
	printf("STRCAT: %s\n", ft_strcat(s, s2));
}

void	test_mem()
{
	
		char	mems[4];
	
		ft_bzero(mems, 4);
		printf("str[0] = %d\n", mems[0]);
		printf("str[1] = %d\n", mems[1]);
		printf("str[2] = %d\n", mems[2]);
		printf("str[3] = %d\n", mems[3]);
		printf("--- memseting with 'a' ---\n");
		ft_memset(mems, 'a', 4);
		printf("str[0] = %c (%d)\n", mems[0], mems[0]);
		printf("str[1] = %c (%d)\n", mems[1], mems[1]);
		printf("str[2] = %c (%d)\n", mems[2], mems[2]);
		printf("str[3] = %c (%d)\n", mems[3], mems[3]);

		char * tata = strdup("yolo");
		char * toto = ft_strdup(tata);
		char * titi = NULL;
	
		printf(" original string : [%p] [%s]\n", tata, tata);
		printf("duplicate string : [%p] [%s]\n", toto, toto);
	
		printf("test with null :\n");
		printf("titi = %s\n", titi);
		titi = ft_strdup(NULL);
		printf("titi = %s\n", titi);
}
/*
int main()
{
	char	num;
	char	letter;

	letter = 'a';
	num = '2';
	printf("\\0 -- > %d\n", '\0');
	printf("%c is num? %d\n%c is num? %d\n", num, ft_isdigit(num), letter, ft_isdigit(letter));
	printf("%c is alpha? %d\n%c is alpha? %d\n", num, ft_isalpha(num), letter, ft_isalpha(letter));
	printf("%c is alnum? %d\n%c is alnum? %d\n", num, ft_isalnum(num), letter, ft_isalnum(letter));
	printf("%c is ascii? %d\n%c is ascii? %d\nma== > 73242 isascii? %d\n", num, ft_isascii(num), letter, ft_isascii(letter), ft_isascii(73291));
	printf("%c is print? %d\n%c is print? %d\nma== > \\t isprint? %d\n", num, ft_isprint(num), letter, ft_isprint(letter), ft_isprint('\t'));
	printf("%c -toupper-> %c | %c -tolower-> %c\n", letter, ft_toupper(letter), 'A', ft_tolower('A'));
	printf("%c -toupper-> %c | %c -tolower-> %c\n", 'z', ft_toupper('z'), 'Z', ft_tolower('Z'));
	printf("%d -toupper-> %d | %d -tolower-> %d\n", 10, ft_toupper(10), 233, ft_tolower(233));
	
	test_bzero();
	test_strlen();
	test_mem();
	return (0);
}*/

int main(int ac, char ** av)
{
	// int ret = 0;
	if (ac == 1) {
		ft_cat(0);
	}
	else if (ac == 2) {
		int fd = open(av[1], O_RDONLY);
		ft_cat(fd);
		if (fd != -1) {
			close(fd);
		} else {
			printf("error\n");
		}
	}
	else {
		printf("usage: %s [file]\n", av[0]);
	}
	return (0);
}