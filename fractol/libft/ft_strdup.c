/* ************************************************************************** */
/*                                                                            */
/*                                                        :::      ::::::::   */
/*   ft_strdup.c                                        :+:      :+:    :+:   */
/*                                                    +:+ +:+         +:+     */
/*   By: tktorza <marvin@42.fr>                     +#+  +:+       +#+        */
/*                                                +#+#+#+#+#+   +#+           */
/*   Created: 2015/11/24 15:40:05 by tktorza           #+#    #+#             */
/*   Updated: 2015/12/04 15:40:46 by tktorza          ###   ########.fr       */
/*                                                                            */
/* ************************************************************************** */

#include "libft.h"

char	*ft_strdup(const char *s)
{
	char	*memory;
	int		i;

	i = 0;
	memory = (char *)malloc(sizeof(char) * (ft_strlen(s) + 1));
	if (memory == NULL)
		return (NULL);
	while (s[i] != '\0')
	{
		memory[i] = s[i];
		i++;
	}
	memory[i] = '\0';
	return (memory);
}
