/* ************************************************************************** */
/*                                                                            */
/*                                                        :::      ::::::::   */
/*   ft_atoi.c                                          :+:      :+:    :+:   */
/*                                                    +:+ +:+         +:+     */
/*   By: tktorza <tktorza@student.42.fr>            +#+  +:+       +#+        */
/*                                                +#+#+#+#+#+   +#+           */
/*   Created: 2015/11/25 12:25:22 by tktorza           #+#    #+#             */
/*   Updated: 2017/10/18 14:05:04 by tktorza          ###   ########.fr       */
/*                                                                            */
/* ************************************************************************** */

#include "../inc/libft.h"

int		ft_atoi(char *str)
{
	int		i;
	int		sign;
	int		result;

	result = 0;
	i = 0;
	if (!str)
		return 0;
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