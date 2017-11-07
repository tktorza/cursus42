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

int main()
{
	char	num;
	char	letter;

	letter = 'a';
	num = '2';
	printf("%c is num? %d\n%c is num? %d\n", num, ft_isdigit(num), letter, ft_isdigit(letter));
	printf("%c is alpha? %d\n%c is alpha? %d\n", num, ft_isalpha(num), letter, ft_isalpha(letter));
	printf("%c is alnum? %d\n%c is alnum? %d\n", num, ft_isalnum(num), letter, ft_isalnum(letter));
	printf("%c is ascii? %d\n%c is ascii? %d\n== > 73242 isascii? %d\n", num, ft_isascii(num), letter, ft_isascii(letter), ft_isascii(73291));

	return (0);
}