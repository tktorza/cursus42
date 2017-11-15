/* ************************************************************************** */
/*                                                                            */
/*                                                        :::      ::::::::   */
/*   ft_strnew.c                                        :+:      :+:    :+:   */
/*                                                    +:+ +:+         +:+     */
/*   By: tktorza <tktorza@student.42.fr>            +#+  +:+       +#+        */
/*                                                +#+#+#+#+#+   +#+           */
/*   Created: 2015/11/26 09:31:21 by tktorza           #+#    #+#             */
/*   Updated: 2017/10/18 14:06:19 by tktorza          ###   ########.fr       */
/*                                                                            */
/* ************************************************************************** */

#include <stdlib.h>
#include "../inc/libft.h"

char	*ft_strnew(size_t size)
{
	char	*t;
	int		i;

	i = 0;
	t = malloc(sizeof(char) * size + 1);
	if (t == NULL)
		return (NULL);
	while (t[i] != '\0')
	{
		t[i] = '\0';
		i++;
	}
	t[i] = '\0';
	return (t);
}
