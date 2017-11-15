/* ************************************************************************** */
/*                                                                            */
/*                                                        :::      ::::::::   */
/*   ft_strdup.c                                        :+:      :+:    :+:   */
/*                                                    +:+ +:+         +:+     */
/*   By: tktorza <tktorza@student.42.fr>            +#+  +:+       +#+        */
/*                                                +#+#+#+#+#+   +#+           */
/*   Created: 2015/11/23 17:26:47 by tktorza           #+#    #+#             */
/*   Updated: 2017/10/18 14:06:03 by tktorza          ###   ########.fr       */
/*                                                                            */
/* ************************************************************************** */

#include <stdlib.h>
#include "../inc/libft.h"

char	*ft_strdup(const char *s1)
{
	char		*s2;
	int			i;

	i = -1;
	s2 = malloc(sizeof(char) * (ft_strlen(s1) + 1));
	if (s2 == NULL)
		return (NULL);
	while (s1[++i] != '\0')
		s2[i] = s1[i];
	s2[i] = '\0';
	return (char *)(s2);
}
