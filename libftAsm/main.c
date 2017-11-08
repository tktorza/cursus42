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

void	test_bzero()
{
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
}

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
	return (0);
}