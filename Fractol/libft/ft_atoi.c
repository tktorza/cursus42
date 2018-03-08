/* ************************************************************************** */
/*                                                                            */
/*                                                        :::      ::::::::   */
/*   ft_atoi.c                                          :+:      :+:    :+:   */
/*                                                    +:+ +:+         +:+     */
/*   By: tktorza <marvin@42.fr>                     +#+  +:+       +#+        */
/*                                                +#+#+#+#+#+   +#+           */
/*   Created: 2015/11/23 12:32:38 by tktorza           #+#    #+#             */
/*   Updated: 2015/12/03 18:00:59 by tktorza          ###   ########.fr       */
/*                                                                            */
/* ************************************************************************** */

#include "libft.h"

int		ft_atoi(const char *str)
{
	int		i;
	int		sign;
	int		result;

	result = 0;
	i = 0;
	while (0 < str[i] && str[i] < 33)
		i++;
	if (str[i] == 43 || str[i] == 45)
	{
		sign = 44 - str[i];
		i++;
	}
	else
		sign = 1;
	while (str[i] >= 48 && str[i] <= 57)
		result = (result * 10) + (str[i++] - 48);
	return (result * sign);
}
